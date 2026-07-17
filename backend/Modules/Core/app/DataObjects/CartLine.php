<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

use Modules\Core\ValueObjects\Money;

/**
 * One line in a cart: a product variant, its quantity and the derived totals.
 * Immutable snapshot — the Cart module (Task 6) builds these from its storage.
 */
readonly class CartLine
{
    public function __construct(
        public string $variantId,
        public string $name,
        public Money $unitPrice,
        public int $quantity,
        public Money $lineTotal,
    ) {
    }
}
