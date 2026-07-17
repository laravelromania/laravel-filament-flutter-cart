<?php

declare(strict_types=1);

namespace Modules\Payments\Drivers;

use Illuminate\Http\Request;
use Modules\Core\Contracts\Payable;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\DataObjects\PaymentResult;

/**
 * Shared machinery for the real gateways (Netopia, PayU). Both implement the Core
 * {@see PaymentGateway} contract identically in SANDBOX and diverge only in their
 * live provider calls, so the sandbox flow — initiate → simulator → signed
 * callback → verify → PaymentResult — lives here once.
 *
 * SANDBOX is the default and is FORCED whenever credentials are missing, so the
 * tutorial runs end-to-end with zero real merchant accounts and never risks a
 * live charge. Each concrete driver fills in the four `*Live()` hooks with the
 * real provider integration (and a comment pointing at the exact crypto).
 */
abstract class AbstractGateway implements PaymentGateway
{
    public function __construct(
        protected readonly bool $sandbox,
        protected readonly string $sandboxSecret,
    ) {
    }

    abstract public function code(): string;

    abstract public function label(): string;

    /** Sandbox unless it was explicitly disabled AND the real credentials exist. */
    abstract public function isSandbox(): bool;

    public function initiate(Payable $order): PaymentRedirect
    {
        if ($this->isSandbox()) {
            // No bank in reach: hand the shopper to the internal "simulează plata"
            // screen. The merchant reference we would send a real gateway is the
            // order's public reference; the callback echoes it straight back.
            return new PaymentRedirect(
                url: route('payments.simulate', [
                    'gateway' => $this->code(),
                    'reference' => $order->payableReference(),
                ]),
            );
        }

        return $this->initiateLive($order);
    }

    public function handleCallback(Request $request): PaymentResult
    {
        if ($this->isSandbox()) {
            $status = (string) $request->input('status', '');

            return new PaymentResult(
                success: $status === 'confirmed',
                reference: (string) $request->input('reference', ''),
                rawStatus: $status !== '' ? $status : 'unknown',
            );
        }

        return $this->handleLiveCallback($request);
    }

    public function refund(Payable $order): bool
    {
        return $this->isSandbox() ? true : $this->refundLive($order);
    }

    /**
     * THE security gate. A callback is an untrusted, publicly reachable POST — we
     * never move an order to "paid" on its say-so until the signature proves the
     * gateway (not an attacker) sent it.
     */
    public function verifySignature(Request $request): bool
    {
        if ($this->isSandbox()) {
            // SANDBOX: the simulator signs the callback with an HMAC over
            // (gateway|reference|status) keyed by sandbox_secret; we recompute it
            // and compare in constant time. A tampered or absent signature is
            // rejected — exactly as a forged live IPN would be. In PRODUCTION the
            // check is the provider's real one (see verifyLiveSignature()).
            return hash_equals(
                $this->sandboxSignature(
                    (string) $request->input('reference', ''),
                    (string) $request->input('status', ''),
                ),
                (string) $request->input('signature', ''),
            );
        }

        return $this->verifyLiveSignature($request);
    }

    /** The signature the sandbox simulator posts; the callback recomputes it. */
    public function sandboxSignature(string $reference, string $status): string
    {
        return hash_hmac('sha256', $this->code().'|'.$reference.'|'.$status, $this->sandboxSecret);
    }

    // --- Live provider hooks (real integration per gateway) -------------------

    abstract protected function initiateLive(Payable $order): PaymentRedirect;

    abstract protected function handleLiveCallback(Request $request): PaymentResult;

    abstract protected function verifyLiveSignature(Request $request): bool;

    abstract protected function refundLive(Payable $order): bool;
}
