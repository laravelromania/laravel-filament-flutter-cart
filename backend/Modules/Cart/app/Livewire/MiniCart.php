<?php

declare(strict_types=1);

namespace Modules\Cart\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Core\Contracts\CartRepository;

/**
 * The header basket button: an icon plus a small badge with the total number of
 * items. It re-reads the count whenever `cart-updated` fires (from the drawer,
 * the cart page, or a merge on login) so the badge is always live. Clicking it
 * asks the drawer to open via a browser `open-cart` event.
 */
class MiniCart extends Component
{
    public int $itemCount = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->itemCount = app(CartRepository::class)->get()->itemCount;
    }

    public function render(): View
    {
        return view('cart::livewire.mini-cart');
    }
}
