<?php

declare(strict_types=1);

namespace Modules\Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

/**
 * The full product for the detail screen: description, images, categories and —
 * the point of the endpoint — the purchasable variants, each with its effective
 * price and the attribute picks (Culoare: Roșu, Mărime: M) that identify it. The
 * Flutter app renders a variant selector from `variants[].attributes`.
 *
 * @property-read Product $resource
 */
class ProductDetailResource extends JsonResource
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
            'description' => $product->description,
            'brand' => $product->brand?->name,
            'price' => MoneyResource::make($product->displayPrice()),
            'in_stock' => $product->variants->isEmpty()
                || $product->variants->contains(fn (ProductVariant $variant): bool => (int) $variant->stock > 0),
            'images' => $product->getMedia('images')->map->getUrl()->all(),
            'categories' => $product->categories->map(fn ($category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->all(),
            'variants' => $product->variants
                ->sortBy('id')
                ->values()
                ->map(fn (ProductVariant $variant): array => $this->variant($variant))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function variant(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => MoneyResource::make($variant->effectivePrice()),
            'stock' => (int) $variant->stock,
            'in_stock' => $variant->inStock(),
            'attributes' => $variant->attributeValues->map(fn ($value): array => [
                'attribute' => $value->attribute?->name,
                'value' => $value->value,
                'slug' => $value->slug,
            ])->all(),
        ];
    }
}
