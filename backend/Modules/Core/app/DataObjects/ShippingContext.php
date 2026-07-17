<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

use Modules\Core\ValueObjects\Money;

/**
 * Everything a ShippingProvider needs to quote a delivery, without knowing about
 * carts or orders. Built by Checkout (Task 8) and passed to each provider's
 * quote(). Keeps Core free of Cart/Order concrete types.
 */
readonly class ShippingContext
{
    public function __construct(
        public string $county,
        public string $city,
        public string $postalCode,
        public float $weightKg,
        public Money $cartSubtotal,
    ) {
    }
}
