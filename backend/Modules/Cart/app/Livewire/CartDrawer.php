<?php

declare(strict_types=1);

namespace Modules\Cart\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\CartData;

/**
 * The slide-out basket. It is the single handler of the `add-to-cart` event that
 * Catalog's product page (Part 5) dispatches — this is where that intent finally
 * mutates the cart. Its own mutations re-announce `cart-updated` so the mini-cart
 * badge refreshes; adding also fires `open-cart` so the drawer slides open. It
 * reads the cart through the CartRepository contract, never caring whether the
 * basket lives in the session or the database.
 */
class CartDrawer extends Component
{
    /**
     * Add a variant to the cart. Param names MUST match the payload dispatched by
     * Catalog's ProductShow: `$this->dispatch('add-to-cart', variantId: .., qty: ..)`.
     */
    #[On('add-to-cart')]
    public function addToCart($variantId, $qty = 1): void
    {
        $this->cart()->add((string) $variantId, max(1, (int) $qty));

        $this->announce();
        $this->dispatch('open-cart');
    }

    public function updateQty(string $variantId, int $qty): void
    {
        $this->cart()->update($variantId, $qty);
        $this->announce();
    }

    public function remove(string $variantId): void
    {
        $this->cart()->remove($variantId);
        $this->announce();
    }

    /**
     * Refresh when the cart was changed elsewhere (mini-cart, cart page). This
     * only invalidates the cached view — it must NOT re-announce `cart-updated`,
     * or listening to our own announcement would loop.
     */
    #[On('cart-updated')]
    public function onCartUpdated(): void
    {
        unset($this->data);
    }

    #[Computed]
    public function data(): CartData
    {
        return $this->cart()->get();
    }

    public function render(): View
    {
        return view('cart::livewire.cart-drawer');
    }

    /**
     * Invalidate our cached snapshot and tell the rest of the header the cart
     * changed.
     */
    private function announce(): void
    {
        unset($this->data);
        $this->dispatch('cart-updated');
    }

    private function cart(): CartRepository
    {
        return app(CartRepository::class);
    }
}
