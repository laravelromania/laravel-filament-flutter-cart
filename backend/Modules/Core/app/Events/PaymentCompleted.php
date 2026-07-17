<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Core\DataObjects\PaymentResult;

/**
 * The cross-module integration event fired once a payment has been verified.
 * Payments (Part 11) dispatches it after normalising a gateway callback into a
 * {@see PaymentResult}; Orders (Part 9) listens to move the matching order to
 * "paid". It lives in Core — like {@see OrderPlaced} — so Orders depends on a
 * Core type and never on the Payments module.
 *
 * Dormant until Part 11 (nothing dispatches it yet), but shipped now so Orders
 * owns every mutation of an Order from day one.
 */
readonly class PaymentCompleted
{
    use Dispatchable;

    public function __construct(
        public string $orderReference,
        public PaymentResult $result,
    ) {
    }
}
