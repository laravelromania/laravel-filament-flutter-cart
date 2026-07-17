<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Account;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Skeleton only. Orders (Part 9) belongs to a Customer; once that module
 * lands, this page reads `$this->customer()->orders` (or similar) and lists
 * them. For now it exists so `/cont/comenzi` is a real link.
 */
#[Layout('core::layouts.storefront')]
#[Title('Comenzile mele')]
class Orders extends Component
{
    public function render(): View
    {
        return view('customers::livewire.account.orders');
    }
}
