<?php

declare(strict_types=1);

namespace Modules\Orders\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Core\DataObjects\AddressData;
use Modules\Core\Events\OrderPlaced;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Mail\OrderConfirmed;
use Modules\Orders\Models\Order;

/**
 * Turns a Core {@see OrderPlaced} event into a persisted Order + OrderItems.
 *
 * This is the whole reason Orders exists: Checkout emitted a self-contained
 * OrderDraft and cleared the cart; here we materialise it. The order starts in
 * {@see OrderStatus::Pending} and the shopper gets a queued confirmation email.
 *
 * Idempotency: an OrderDraft carries no stable identity, so a re-dispatched
 * event would create a second order. In this series OrderPlaced is fired
 * exactly once per checkout submit (a synchronous, in-request event), so we do
 * not de-duplicate here; a real store would attach an idempotency key to the
 * draft and guard on it.
 */
class CreateOrderFromCheckout
{
    public function handle(OrderPlaced $event): void
    {
        $draft = $event->draft;

        $order = Order::create([
            'status' => OrderStatus::Pending,
            'user_id' => $draft->userId,
            'email' => $draft->email,
            'customer_name' => $draft->customerName,
            'phone' => $draft->phone,
            'billing' => $this->addressToArray($draft->billing),
            'shipping' => $this->addressToArray($draft->shipping),
            'items_subtotal' => $draft->itemsSubtotal,
            'shipping_code' => $draft->shippingCode,
            'shipping_label' => $draft->shippingLabel,
            'shipping_total' => $draft->shippingCost,
            'payment_code' => $draft->paymentCode,
            'total' => $draft->total,
        ]);

        foreach ($draft->lines as $line) {
            $order->items()->create([
                'variant_id' => $line->variantId !== '' ? (int) $line->variantId : null,
                'name' => $line->name,
                'unit_price' => $line->unitPrice,
                'quantity' => $line->quantity,
                'line_total' => $line->lineTotal,
            ]);
        }

        Mail::to($order->email)->queue(new OrderConfirmed($order->load('items')));
    }

    /**
     * @return array<string, string>
     */
    private function addressToArray(AddressData $address): array
    {
        return [
            'name' => $address->name,
            'phone' => $address->phone,
            'county' => $address->county,
            'city' => $address->city,
            'street' => $address->street,
            'postalCode' => $address->postalCode,
        ];
    }
}
