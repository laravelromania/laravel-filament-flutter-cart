<?php

declare(strict_types=1);

namespace Modules\Core\DataObjects;

use Modules\Core\ValueObjects\Money;

/**
 * The whole cart as a value: the lines plus derived totals. Returned by every
 * CartRepository implementation (Task 6) and serialized to JSON by the API
 * (Task 12), so the storefront and the Flutter app read the same shape.
 */
readonly class CartData
{
    /**
     * @param  CartLine[]  $lines
     */
    public function __construct(
        public array $lines,
        public Money $subtotal,
        public int $itemCount,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }
}
