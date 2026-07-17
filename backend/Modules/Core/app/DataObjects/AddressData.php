<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

/**
 * A postal address as a plain value — no persistence, no `type` column, no
 * customer_id. Checkout (Part 8) builds these from a guest form or from a
 * Customers\Address row; Orders (Part 9) will copy them onto the order. Living
 * in Core means the OrderPlaced payload can carry addresses without any module
 * depending on the Customers module's Eloquent model.
 */
readonly class AddressData
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $county,
        public string $city,
        public string $street,
        public string $postalCode,
    ) {
    }
}
