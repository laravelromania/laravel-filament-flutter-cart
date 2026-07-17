<?php

declare(strict_types=1);

namespace Modules\Orders\Livewire\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\Orders\Models\Order;

/**
 * The signed-in shopper's order list, registered as `orders.account-orders`.
 *
 * It is embedded (not routed): the Customers module's `/cont/comenzi` page
 * renders it with `@livewire('orders.account-orders')`, so Orders owns the query
 * and the markup while Customers keeps only a string reference. Orders never
 * imports a Customers class and vice-versa.
 */
class OrderHistory extends Component
{
    /**
     * @return Collection<int, Order>
     */
    #[Computed]
    public function orders(): Collection
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('orders::livewire.account.order-history');
    }
}
