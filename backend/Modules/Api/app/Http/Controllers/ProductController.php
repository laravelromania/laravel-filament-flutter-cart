<?php

declare(strict_types=1);

namespace Modules\Api\Http\Controllers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Api\Http\Resources\ProductDetailResource;
use Modules\Api\Http\Resources\ProductResource;
use Modules\Catalog\Models\Product;

/**
 * The public catalog for the mobile app. `index` mirrors the storefront's
 * {@see \Modules\Catalog\Livewire\Concerns\FiltersProducts} query — same active
 * scope, same filters (search, brand, price band, category), same sorts — but
 * reads them from the query string and returns a paginated JSON collection. The
 * shared eager-loading keeps a listing free of the N+1 flagged back in Part 4.
 */
class ProductController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(50, max(1, (int) $request->integer('per_page', 12)));

        return ProductResource::collection(
            $this->query($request)->paginate($perPage)->withQueryString(),
        );
    }

    public function show(Product $product): ProductDetailResource
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand',
            'categories',
            'variants.attributeValues.attribute',
            'variants.product',
            'media',
        ]);

        return new ProductDetailResource($product);
    }

    /**
     * @return EloquentBuilder<Product>
     */
    private function query(Request $request): EloquentBuilder
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'categories', 'variants.product', 'media']);

        if (($search = trim((string) $request->query('search', ''))) !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if (($brand = (string) $request->query('brand', '')) !== '') {
            $query->whereHas('brand', fn (Builder $q) => $q->where('slug', $brand));
        }

        if (($category = (string) $request->query('category', '')) !== '') {
            $query->whereHas('categories', fn (Builder $q) => $q->where('slug', $category));
        }

        if (is_numeric($min = $request->query('price_min'))) {
            $query->where('price', '>=', (int) round(((float) $min) * 100));
        }

        if (is_numeric($max = $request->query('price_max'))) {
            $query->where('price', '<=', (int) round(((float) $max) * 100));
        }

        return match ((string) $request->query('sort', 'nou')) {
            'pret-asc' => $query->orderBy('price'),
            'pret-desc' => $query->orderByDesc('price'),
            'nume' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }
}
