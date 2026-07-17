<?php

declare(strict_types=1);

namespace Modules\Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Models\Product;

/**
 * A catalog product as it appears in a listing card: enough to render a grid in
 * the Flutter app without a second request. The price is the product's
 * {@see Product::displayPrice()} (the default variant's effective price) in the
 * shared Money shape; the detail endpoint adds variants and attributes.
 *
 * @property-read Product $resource
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $this->whenLoaded('brand', fn () => $product->brand?->name),
            'price' => MoneyResource::make($product->displayPrice()),
            'in_stock' => $this->inStock(),
            'image' => $product->getFirstMediaUrl('images') ?: null,
        ];
    }

    /**
     * In-stock from the already eager-loaded `variants` when available (the list
     * query loads them), so a grid never fires a query per card; falls back to the
     * model's own check otherwise.
     */
    private function inStock(): bool
    {
        $product = $this->resource;

        if ($product->relationLoaded('variants')) {
            return $product->variants->isEmpty()
                || $product->variants->contains(fn ($variant): bool => (int) $variant->stock > 0);
        }

        return $product->inStock();
    }
}
