<?php

declare(strict_types=1);

namespace Modules\Orders\Listeners;

use Modules\Core\Events\PaymentCompleted;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

/**
 * Moves an order to `paid` when a payment is confirmed.
 *
 * Payments (Part 11) will dispatch {@see PaymentCompleted} after verifying a
 * gateway callback. Orders is the single owner of every Order mutation, so the
 * transition lives here — not in Payments. Dormant until Part 11, but correct.
 *
 * Idempotent: a duplicate/late callback for an already-paid order is a no-op,
 * and the {@see OrderStatus} graph is honoured (only pending -> paid is valid).
 */
class MarkOrderPaid
{
    public function handle(PaymentCompleted $event): void
    {
        if (! $event->result->success) {
            return;
        }

        $order = Order::where('number', $event->orderReference)->first();

        if ($order === null) {
            return;
        }

        if ($order->status === OrderStatus::Paid || $order->paid_at !== null) {
            return;
        }

        if (! $order->status->canTransitionTo(OrderStatus::Paid)) {
            return;
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
