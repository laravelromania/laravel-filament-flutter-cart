<?php

declare(strict_types=1);

namespace Modules\Checkout\Drivers;

use Illuminate\Http\Request;
use Modules\Core\Contracts\Payable;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\DataObjects\PaymentResult;

/**
 * A stand-in gateway that never touches a real processor. `initiate()` points the
 * shopper at an internal "plată simulată" page instead of a bank; `handleCallback()`
 * always reports success; `refund()` always succeeds. It implements the real
 * {@see PaymentGateway} contract so Payments (Part 11) can drop in Netopia/PayU
 * without changing a line of Checkout.
 */
class MockPaymentGateway implements PaymentGateway
{
    public function code(): string
    {
        return 'mock';
    }

    public function label(): string
    {
        return 'Plată simulată (mediu de test)';
    }

    public function initiate(Payable $order): PaymentRedirect
    {
        return new PaymentRedirect(
            url: route('storefront.checkout.mock-payment', ['ref' => $order->payableReference()]),
        );
    }

    public function handleCallback(Request $request): PaymentResult
    {
        return new PaymentResult(
            success: true,
            reference: (string) $request->query('ref', ''),
            rawStatus: 'PAID',
        );
    }

    public function refund(Payable $order): bool
    {
        return true;
    }
}
