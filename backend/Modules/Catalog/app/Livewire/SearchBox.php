<?php

declare(strict_types=1);

namespace Modules\Catalog\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The header search field. It is intentionally tiny: on submit it navigates to
 * the products listing carrying the term as `?search=`, letting ProductIndex do
 * the actual filtering. `wire:navigate` keeps it a snappy SPA-like hop.
 */
class SearchBox extends Component
{
    public string $q = '';

    public function search(): void
    {
        $parameters = $this->q !== '' ? ['search' => $this->q] : [];

        $this->redirectRoute('storefront.products', $parameters, navigate: true);
    }

    public function render(): View
    {
        return view('catalog::livewire.search-box');
    }
}
