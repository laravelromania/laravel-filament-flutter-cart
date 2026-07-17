<?php

declare(strict_types=1);

namespace Modules\Catalog\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Catalog\Livewire\Concerns\FiltersProducts;
use Modules\Catalog\Models\Category;

/**
 * Category landing page (`/categorii/{category:slug}`). It is a ProductIndex
 * with the category locked by the route: the same filters/sort/pagination apply
 * on top of a fixed category, and the exact same grid partial is rendered.
 */
#[Layout('core::layouts.storefront')]
class CategoryShow extends Component
{
    use FiltersProducts;

    public Category $category;

    public function mount(Category $category): void
    {
        $this->category = $category;
    }

    protected function applyCategoryFilter(Builder $query): void
    {
        $query->whereHas('categories', fn (Builder $q) => $q->whereKey($this->category->getKey()));
    }

    public function render(): View
    {
        return view('catalog::livewire.category-show', [
            'products' => $this->productsQuery()->paginate(12),
        ])->title($this->category->name.' · Produse');
    }
}
