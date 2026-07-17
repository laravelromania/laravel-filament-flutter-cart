<?php

declare(strict_types=1);

namespace Modules\Catalog\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

/**
 * Product detail page (`/produse/{product:slug}`). Shows the gallery, a variant
 * selector and the price of the currently selected variant. "Adaugă în coș"
 * only *dispatches* a Livewire `add-to-cart` event (plus a toast); the Cart
 * module in Part 6 is what listens and actually mutates the basket.
 */
#[Layout('core::layouts.storefront')]
class ProductShow extends Component
{
    public Product $product;

    /** Currently selected variant id (defaults to the product's default variant). */
    public ?int $selectedVariant = null;

    public int $qty = 1;

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);

        $this->product = $product->load([
            'brand',
            'categories',
            'variants.attributeValues.attribute',
            'variants.product',
            'media',
        ]);

        $this->selectedVariant = $this->product->variants->sortBy('id')->first()?->getKey();
    }

    /** The variant reflected by the current selection (or the default one). */
    #[Computed]
    public function variant(): ?ProductVariant
    {
        if ($this->selectedVariant !== null) {
            $variant = $this->product->variants->firstWhere('id', $this->selectedVariant);

            if ($variant !== null) {
                return $variant;
            }
        }

        return $this->product->variants->sortBy('id')->first();
    }

    public function selectVariant(int $variantId): void
    {
        $this->selectedVariant = $variantId;
    }

    /**
     * Announce the intent to add a variant to the cart. Part 6's Cart component
     * listens for `add-to-cart`; here we only dispatch it and flash a toast so
     * the button already feels alive.
     */
    public function addToCart(?int $variantId = null): void
    {
        $variantId ??= $this->selectedVariant;

        if ($variantId === null) {
            return;
        }

        $this->dispatch('add-to-cart', variantId: $variantId, qty: $this->qty);
        $this->dispatch('cart-toast', message: 'Produs adăugat în coș.');
    }

    public function render(): View
    {
        return view('catalog::livewire.product-show')
            ->title($this->product->name.' · Produse');
    }
}
