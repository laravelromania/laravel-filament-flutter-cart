<?php

declare(strict_types=1);

namespace Modules\Payments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\Checkout\Services\PaymentManager;
use Modules\Core\Contracts\OrderLocator;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\Events\PaymentCompleted;
use Modules\Payments\Drivers\AbstractGateway;

/**
 * The Payments HTTP surface. It never touches an Order model: it loads the
 * {@see \Modules\Core\Contracts\Payable} through the Core {@see OrderLocator} and
 * signals success back to Orders with the Core {@see PaymentCompleted} event.
 */
class PaymentController
{
    /**
     * Entry point from the confirmation page: resolve the order by its reference,
     * ask its chosen gateway to initiate, and hand the shopper over.
     */
    public function initiate(string $reference)
    {
        $order = app(OrderLocator::class)->byReference($reference);

        abort_if($order === null, 404);

        $redirect = app(PaymentManager::class)
            ->get($order->paymentMethodCode())
            ->initiate($order);

        // Remember where to bounce the browser once the payment settles — the
        // callback/return only carry the merchant reference, not this URL token.
        session(['payments.return_reference' => $reference]);

        return $this->follow($redirect);
    }

    /**
     * The gateway callback / IPN — publicly reachable and CSRF-exempt. Verify the
     * signature BEFORE trusting anything; only a verified success moves the order.
     */
    public function callback(Request $request, string $gateway)
    {
        $driver = $this->driver($gateway);

        if (! $driver->verifySignature($request)) {
            Log::warning('Semnătură callback de plată invalidă — respins.', [
                'gateway' => $gateway,
                'ip' => $request->ip(),
                'reference' => $request->input('reference'),
            ]);

            abort(403, 'Semnătură invalidă.');
        }

        $result = $driver->handleCallback($request);

        if ($result->success) {
            // Orders' MarkOrderPaid listens and is idempotent, so a replayed IPN
            // for an order already paid is a harmless no-op.
            PaymentCompleted::dispatch($result->reference, $result);
        }

        // A browser (the sandbox simulator) is sent to the confirmation page; a
        // real server-to-server IPN just receives a plain 200 acknowledgement.
        $return = session('payments.return_reference');

        if ($return !== null && Route::has('storefront.order.confirmation')) {
            return redirect()->route('storefront.order.confirmation', $return);
        }

        return response('OK', 200);
    }

    /** Browser return after a real gateway redirect: back to the confirmation. */
    public function returnFromGateway(string $gateway, string $reference)
    {
        abort_unless(Route::has('storefront.order.confirmation'), 404);

        return redirect()->route(
            'storefront.order.confirmation',
            session('payments.return_reference', $reference),
        );
    }

    /**
     * The sandbox-only "simulează plata" screen. Its Succes/Eșec buttons POST a
     * correctly-signed callback, so the whole verify → dispatch → mark-paid path
     * is demonstrable without a merchant account.
     */
    public function simulate(string $gateway, string $reference)
    {
        $driver = $this->driver($gateway);

        abort_unless($driver->isSandbox(), 404);

        return view('payments::simulate', [
            'gateway' => $gateway,
            'reference' => $reference,
            'label' => $driver->label(),
            'callbackUrl' => route('payments.callback', ['gateway' => $gateway]),
            'successSignature' => $driver->sandboxSignature($reference, 'confirmed'),
            'failSignature' => $driver->sandboxSignature($reference, 'canceled'),
        ]);
    }

    /** Follow a PaymentRedirect: a GET is a plain redirect, a POST auto-submits. */
    private function follow(PaymentRedirect $redirect)
    {
        if ($redirect->method === 'POST') {
            return view('payments::redirect', ['redirect' => $redirect]);
        }

        return redirect()->away($redirect->url);
    }

    /** Resolve a Payments gateway (Netopia/PayU) by code, or 404. */
    private function driver(string $gateway): AbstractGateway
    {
        $manager = app(PaymentManager::class);

        abort_unless($manager->has($gateway), 404);

        $driver = $manager->get($gateway);

        abort_unless($driver instanceof AbstractGateway, 404);

        return $driver;
    }
}
