<?php

declare(strict_types=1);

namespace Modules\Orders\Livewire\Storefront;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Orders\Models\Order;

/**
 * The post-checkout order confirmation at `/comanda/{reference}` (route
 * `storefront.order.confirmation`), where the wizard lands the shopper. It is the
 * hand-off point to Payments: for an unpaid order paid online it shows a
 * "Plătește" button linking to the Payments `payments.initiate` route — as a
 * plain named-route string, so Orders never imports the Payments module and the
 * button simply hides when Payments is not installed.
 *
 * Access: the order is addressed by its UUID `reference`, an unguessable token
 * that stands in as the guest capability. An order that belongs to a registered
 * shopper is additionally gated on ownership (a 403 for anyone else).
 */
#[Layout('core::layouts.storefront')]
#[Title('Comanda ta')]
class OrderConfirmation extends Component
{
    public string $reference = '';

    public function mount(string $reference): void
    {
        $this->reference = $reference;

        $order = $this->order();

        abort_if($order === null, 404);

        if ($order->user_id !== null) {
            abort_unless((int) $order->user_id === (int) Auth::id(), 403);
        }
    }

    #[Computed]
    public function order(): ?Order
    {
        return Order::with('items')
            ->where('reference', $this->reference)
            ->first();
    }

    /**
     * Whether to offer online payment: the order is still unpaid, it was placed
     * with a real online gateway (not the Part-8 'mock' placeholder), and the
     * Payments module has registered its initiate route.
     */
    #[Computed]
    public function canPay(): bool
    {
        $order = $this->order();

        return $order !== null
            && $order->paid_at === null
            && $order->payment_code !== 'mock'
            && Route::has('payments.initiate');
    }

    #[Computed]
    public function payUrl(): ?string
    {
        return $this->canPay()
            ? route('payments.initiate', ['reference' => $this->reference])
            : null;
    }

    public function render(): View
    {
        return view('orders::livewire.storefront.order-confirmation');
    }
}
