<?php

declare(strict_types=1);

namespace Modules\Cart\Services\Concerns;

use Modules\Catalog\Models\ProductVariant;
use Modules\Core\DataObjects\CartData;
use Modules\Core\DataObjects\CartLine;
use Modules\Core\ValueObjects\Money;

/**
 * Turns a plain `[variantId => qty]` map into a {@see CartData}. Shared by both
 * the session- and database-backed carts so the two storages never disagree on
 * how a line is priced or named. The variants are loaded in a single query with
 * their product and attribute values eager-loaded, so building a cart of N lines
 * is one query, not N.
 */
trait BuildsCartData
{
    /**
     * @param  array<int|string, int>  $quantities
     */
    protected function buildCartData(array $quantities): CartData
    {
        $quantities = array_filter($quantities, static fn (int $qty): bool => $qty > 0);

        if ($quantities === []) {
            return new CartData([], Money::of(0), 0);
        }

        $variants = ProductVariant::query()
            ->with('product', 'attributeValues.attribute')
            ->whereIn('id', array_keys($quantities))
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotal = Money::of(0);
        $itemCount = 0;

        foreach ($quantities as $variantId => $qty) {
            $variant = $variants->get((int) $variantId);

            if ($variant === null) {
                // The variant was deleted since it was added — drop the line.
                continue;
            }

            $unitPrice = $variant->effectivePrice();
            $lineTotal = $unitPrice->times($qty);

            $lines[] = new CartLine(
                variantId: (string) $variantId,
                name: $this->lineName($variant),
                unitPrice: $unitPrice,
                quantity: $qty,
                lineTotal: $lineTotal,
            );

            $subtotal = $subtotal->plus($lineTotal);
            $itemCount += $qty;
        }

        return new CartData($lines, $subtotal, $itemCount);
    }

    /**
     * A human label for the line: the product name plus the variant's attribute
     * values (e.g. "Tricou (Roșu · M)"), falling back to just the product name.
     */
    protected function lineName(ProductVariant $variant): string
    {
        $attributes = $variant->attributeValues->pluck('value')->join(' · ');

        return $attributes !== ''
            ? $variant->product->name.' ('.$attributes.')'
            : $variant->product->name;
    }
}
