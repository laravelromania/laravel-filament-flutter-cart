<?php

declare(strict_types=1);

namespace Modules\Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\DataObjects\CartData;
use Modules\Core\DataObjects\CartLine;

/**
 * The shopper's cart as JSON. Wraps a Core {@see CartData} value (not an Eloquent
 * model) — the very same object the storefront renders — so the Flutter app and
 * the web store agree on lines, per-line totals and the subtotal down to the ban.
 *
 * @property-read CartData $resource
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cart = $this->resource;

        return [
            'item_count' => $cart->itemCount,
            'subtotal' => MoneyResource::make($cart->subtotal),
            'lines' => array_map(fn (CartLine $line): array => [
                'variant_id' => $line->variantId,
                'name' => $line->name,
                'quantity' => $line->quantity,
                'unit_price' => MoneyResource::make($line->unitPrice),
                'line_total' => MoneyResource::make($line->lineTotal),
            ], $cart->lines),
        ];
    }
}
