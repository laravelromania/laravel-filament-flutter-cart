<?php

declare(strict_types=1);

namespace Modules\Cart\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Core\Contracts\CartRepository;
use Modules\Core\DataObjects\CartData;

/**
 * The full-page cart at `/cos` (route name `storefront.cart`). Same lines and
 * totals as the drawer, but roomy and linkable — the natural place to review the
 * basket before heading to checkout (Part 8). Reads through CartRepository and
 * announces `cart-updated` on every edit so the header stays in sync.
 */
#[Layout('core::layouts.storefront')]
#[Title('Coșul tău')]
class CartPage extends Component
{
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
        return view('cart::livewire.cart-page');
    }

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
