<?php

declare(strict_types=1);

namespace Modules\Catalog\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Catalog\Livewire\Concerns\FiltersProducts;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;

/**
 * Public product listing (`/produse`). Everything the visitor tweaks — search,
 * category/brand/attribute/price filters, sort and page — lives in the query
 * string so a listing is shareable and back-button friendly.
 */
#[Layout('core::layouts.storefront')]
#[Title('Produse')]
class ProductIndex extends Component
{
    use FiltersProducts;

    /** Free category filter (a category slug), unlike CategoryShow's locked one. */
    #[Url(except: '')]
    public string $category = '';

    protected function applyCategoryFilter(Builder $query): void
    {
        if ($this->category !== '') {
            $query->whereHas('categories', fn (Builder $q) => $q->where('slug', $this->category));
        }
    }

    /** Reset every filter back to its default in one click. */
    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'brand', 'attributeFilters', 'priceMin', 'priceMax', 'sort']);
        $this->resetPage();
    }

    public function render(): View
    {
        return view('catalog::livewire.product-index', [
            'products' => $this->productsQuery()->paginate(12),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'filterAttributes' => Attribute::query()->with('values')->orderBy('name')->get(),
        ]);
    }
}
