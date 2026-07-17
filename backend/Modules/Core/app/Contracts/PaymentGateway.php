<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Illuminate\Http\Request;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\DataObjects\PaymentResult;

/**
 * A payment method. Implemented in the Payments module (Task 11) — Netopia, PayU
 * — and resolved by code() through the PaymentManager (Task 8).
 */
interface PaymentGateway
{
    /** Stable machine code, e.g. 'netopia' | 'payu'. */
    public function code(): string;

    /** Human name shown at checkout. */
    public function label(): string;

    public function initiate(Payable $order): PaymentRedirect;

    public function handleCallback(Request $request): PaymentResult;

    public function refund(Payable $order): bool;
}
