<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Core\DataObjects\OrderDraft;

/**
 * The cross-module integration event: a shopper has placed an order. Checkout
 * (Part 8) dispatches it the instant the cart is turned into an {@see OrderDraft};
 * Orders (Part 9) listens to persist an Order, Shipping (Part 10) and Payments
 * (Part 11) hang their own listeners off the very same event.
 *
 * It deliberately lives in Core, not in Checkout — so every consumer depends on
 * Core (which everyone already depends on) instead of on the Checkout module.
 * That is the whole "dependencies flow toward Core, never toward a sibling"
 * thesis expressed in one class.
 */
readonly class OrderPlaced
{
    use Dispatchable;

    public function __construct(
        public OrderDraft $draft,
    ) {
    }
}
