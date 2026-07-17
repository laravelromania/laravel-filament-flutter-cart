<?php

declare(strict_types=1);

namespace Modules\Orders\Livewire\Account;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Orders\Models\Order;

/**
 * A single order for its owner, registered as `orders.account-order` and routed
 * full-page at `/cont/comenzi/{number}` (route `storefront.account.order`).
 *
 * Ownership is enforced at mount: a shopper can only ever open an order whose
 * `user_id` is their own (a 403 otherwise, a 404 for a bad number). The order is
 * resolved fresh from the number on every request rather than being kept as a
 * serialised model property.
 */
#[Layout('core::layouts.storefront')]
#[Title('Comanda mea')]
class OrderDetail extends Component
{
    public string $number = '';

    public function mount(string $number): void
    {
        $this->number = $number;

        $order = $this->order();

        abort_if($order === null, 404);
        abort_unless((int) $order->user_id === (int) Auth::id(), 403);
    }

    #[Computed]
    public function order(): ?Order
    {
        return Order::with('items')
            ->where('number', $this->number)
            ->first();
    }

    public function render(): View
    {
        return view('orders::livewire.account.order-detail');
    }
}
