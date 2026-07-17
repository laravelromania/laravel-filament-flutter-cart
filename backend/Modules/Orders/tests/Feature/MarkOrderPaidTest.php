<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\DataObjects\PaymentResult;
use Modules\Core\Events\PaymentCompleted;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('moves a pending order to paid on a successful PaymentCompleted', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'paid_at' => null]);

    PaymentCompleted::dispatch($order->number, new PaymentResult(
        success: true,
        reference: $order->number,
        rawStatus: 'confirmed',
    ));

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->paid_at)->not->toBeNull();
});

it('is idempotent — a second success is a no-op', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'paid_at' => null]);

    $result = new PaymentResult(success: true, reference: $order->number, rawStatus: 'confirmed');

    PaymentCompleted::dispatch($order->number, $result);
    $firstPaidAt = $order->fresh()->paid_at;

    PaymentCompleted::dispatch($order->number, $result);

    expect($order->fresh()->paid_at->equalTo($firstPaidAt))->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('ignores a failed payment', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'paid_at' => null]);

    PaymentCompleted::dispatch($order->number, new PaymentResult(
        success: false,
        reference: $order->number,
        rawStatus: 'declined',
    ));

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->paid_at)->toBeNull();
});

it('does nothing for an unknown order reference', function () {
    Order::factory()->create(['status' => OrderStatus::Pending]);

    PaymentCompleted::dispatch('CMD-999999', new PaymentResult(
        success: true,
        reference: 'CMD-999999',
        rawStatus: 'confirmed',
    ));

    expect(Order::where('status', OrderStatus::Paid)->count())->toBe(0);
});
