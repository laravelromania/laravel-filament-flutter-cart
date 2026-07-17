<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

use Modules\Core\ValueObjects\Money;

/**
 * Everything needed to place an order, assembled by Checkout (Part 8) at the
 * moment "Plasează comanda" is clicked and wrapped in the {@see \Modules\Core\Events\OrderPlaced}
 * event. It is a self-contained snapshot: cart lines, chosen addresses, the
 * picked shipping/payment method codes and the computed totals — no live
 * Eloquent models, so any listener (Orders, Shipping, Payments) can rebuild
 * from it without reaching back into Cart or Customers.
 *
 * Lives in Core precisely so those downstream modules listen to a Core type and
 * never depend on Checkout: dependencies keep flowing toward Core.
 */
readonly class OrderDraft
{
    /**
     * @param  CartLine[]  $lines
     */
    public function __construct(
        public ?int $userId,
        public string $email,
        public string $customerName,
        public string $phone,
        public AddressData $billing,
        public AddressData $shipping,
        public array $lines,
        public Money $itemsSubtotal,
        public string $shippingCode,
        public string $shippingLabel,
        public Money $shippingCost,
        public string $paymentCode,
        public Money $total,
    ) {
    }
}
