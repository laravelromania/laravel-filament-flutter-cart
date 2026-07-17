<?php

declare(strict_types=1);

namespace Modules\Catalog\Livewire\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Modules\Catalog\Models\Product;

/**
 * Shared listing behaviour for the storefront: query-string bound filters,
 * sorting and pagination. Reused by {@see \Modules\Catalog\Livewire\ProductIndex}
 * (free category filter) and {@see \Modules\Catalog\Livewire\CategoryShow}
 * (category locked by the route) so the two stay DRY.
 *
 * The list of filters a page exposes is deliberately data — see
 * {@see self::filterProperties()} — so `updating()` can reset pagination for any
 * of them without touching Livewire's own `paginators.*` state.
 */
trait FiltersProducts
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $brand = '';

    /**
     * Selected attribute-value slugs. Named `attributeFilters` (not `attributes`)
     * because Livewire's base component already reserves an `$attributes` property.
     *
     * @var array<int, string>
     */
    #[Url(as: 'atribute')]
    public array $attributeFilters = [];

    #[Url(except: '')]
    public string $priceMin = '';

    #[Url(except: '')]
    public string $priceMax = '';

    #[Url(except: 'nou')]
    public string $sort = 'nou';

    /**
     * Filters that, when changed, must send the visitor back to page one.
     * `category` is only present on ProductIndex but listing it here is
     * harmless for CategoryShow (the property simply never changes there).
     *
     * @return array<int, string>
     */
    protected function filterProperties(): array
    {
        return ['search', 'category', 'brand', 'attributeFilters', 'priceMin', 'priceMax', 'sort'];
    }

    /**
     * Reset pagination whenever a filter changes so the visitor never lands on
     * an out-of-range page. Livewire's own `paginators.page` updates are ignored.
     */
    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, $this->filterProperties(), true)) {
            $this->resetPage();
        }
    }

    /**
     * Base query for every listing: active products with the relations the grid
     * needs eager-loaded. `variants.product` is loaded on purpose so
     * {@see Product::displayPrice()} / `ProductVariant::effectivePrice()` never
     * lazy-loads the parent product (the N+1 flagged in the Part 4 review).
     *
     * @return EloquentBuilder<Product>
     */
    protected function baseQuery(): EloquentBuilder
    {
        return Product::query()
            ->where('is_active', true)
            ->with(['brand', 'categories', 'variants.product', 'media']);
    }

    /**
     * Apply every active filter and the current sort to the base query.
     *
     * @return EloquentBuilder<Product>
     */
    protected function productsQuery(): EloquentBuilder
    {
        $query = $this->baseQuery();

        if ($this->search !== '') {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->brand !== '') {
            $query->whereHas('brand', fn (Builder $q) => $q->where('slug', $this->brand));
        }

        if ($this->attributeFilters !== []) {
            $query->whereHas(
                'variants.attributeValues',
                fn (Builder $q) => $q->whereIn('slug', $this->attributeFilters),
            );
        }

        // Prețul se filtrează pe `products.price` (unități minore). Simplu și
        // suficient pentru serie; limita: nu ține cont de override-ul per variantă.
        if (is_numeric($this->priceMin)) {
            $query->where('price', '>=', (int) round(((float) $this->priceMin) * 100));
        }

        if (is_numeric($this->priceMax)) {
            $query->where('price', '<=', (int) round(((float) $this->priceMax) * 100));
        }

        $this->applyCategoryFilter($query);

        return $this->applySort($query);
    }

    /**
     * @param  EloquentBuilder<Product>  $query
     * @return EloquentBuilder<Product>
     */
    protected function applySort(EloquentBuilder $query): EloquentBuilder
    {
        return match ($this->sort) {
            'pret-asc' => $query->orderBy('price'),
            'pret-desc' => $query->orderByDesc('price'),
            'nume' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }

    /**
     * Hook for the category constraint. ProductIndex applies its `$category`
     * slug filter here; CategoryShow locks the query to its bound category.
     *
     * @param  EloquentBuilder<Product>  $query
     */
    protected function applyCategoryFilter(EloquentBuilder $query): void
    {
        // no-op by default
    }
}
