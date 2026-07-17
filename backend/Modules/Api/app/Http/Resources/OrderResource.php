<?php

declare(strict_types=1);

namespace Modules\Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;

/**
 * A placed order as JSON: the human `number` the shopper quotes, the status as a
 * `{ value, label }` pair (raw for logic, Romanian for display), the frozen
 * address snapshots, the line items and every total in the shared Money shape.
 * `awb` is the courier tracking id once Shipping has created a shipment.
 *
 * @property-read Order $resource
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->resource;

        return [
            'number' => $order->number,
            'reference' => $order->reference,
            'status' => [
                'value' => $order->status->value,
                'label' => $order->status->label(),
            ],
            'email' => $order->email,
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'billing' => $order->billing,
            'shipping' => $order->shipping,
            'items_subtotal' => MoneyResource::make($order->items_subtotal),
            'shipping_code' => $order->shipping_code,
            'shipping_label' => $order->shipping_label,
            'shipping_total' => MoneyResource::make($order->shipping_total),
            'payment_code' => $order->payment_code,
            'total' => MoneyResource::make($order->total),
            'awb' => $order->awb,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $order->items->map(fn (OrderItem $item): array => [
                'variant_id' => $item->variant_id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => MoneyResource::make($item->unit_price),
                'line_total' => MoneyResource::make($item->line_total),
            ])->all()),
        ];
    }
}
