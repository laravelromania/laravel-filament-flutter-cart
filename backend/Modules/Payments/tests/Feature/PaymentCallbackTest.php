<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Modules\Checkout\Services\PaymentManager;
use Modules\Core\Events\PaymentCompleted;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

uses(Tests\TestCase::class, RefreshDatabase::class);

/** A pending Netopia order + the correctly-signed sandbox callback payload for it. */
function netopiaCallback(Order $order, string $status = 'confirmed'): array
{
    $driver = app(PaymentManager::class)->get('netopia');

    return [
        'reference' => $order->number,
        'status' => $status,
        'signature' => $driver->sandboxSignature($order->number, $status),
    ];
}

it('marks the order paid on a signature-verified successful callback', function () {
    Http::fake();

    $order = Order::factory()->create([
        'payment_code' => 'netopia',
        'status' => OrderStatus::Pending,
        'paid_at' => null,
    ]);

    $this->post(route('payments.callback', ['gateway' => 'netopia']), netopiaCallback($order))
        ->assertOk();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->paid_at)->not->toBeNull();

    Http::assertNothingSent();
});

it('rejects a callback with an invalid signature and leaves the order pending', function () {
    $order = Order::factory()->create([
        'payment_code' => 'netopia',
        'status' => OrderStatus::Pending,
        'paid_at' => null,
    ]);

    $forged = netopiaCallback($order);
    $forged['signature'] = 'forged-signature';

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $forged)
        ->assertForbidden();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->paid_at)->toBeNull();
});

it('does not dispatch PaymentCompleted for a forged callback', function () {
    Event::fake([PaymentCompleted::class]);

    $order = Order::factory()->create(['payment_code' => 'netopia', 'status' => OrderStatus::Pending]);

    $forged = netopiaCallback($order);
    $forged['signature'] = 'forged-signature';

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $forged)
        ->assertForbidden();

    Event::assertNotDispatched(PaymentCompleted::class);
});

it('leaves the order pending on a verified but unsuccessful (canceled) callback', function () {
    $order = Order::factory()->create([
        'payment_code' => 'payu',
        'status' => OrderStatus::Pending,
        'paid_at' => null,
    ]);

    $driver = app(PaymentManager::class)->get('payu');

    $this->post(route('payments.callback', ['gateway' => 'payu']), [
        'reference' => $order->number,
        'status' => 'canceled',
        'signature' => $driver->sandboxSignature($order->number, 'canceled'),
    ])->assertOk();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->paid_at)->toBeNull();
});

it('is idempotent — a replayed callback keeps a single paid_at', function () {
    $order = Order::factory()->create([
        'payment_code' => 'netopia',
        'status' => OrderStatus::Pending,
        'paid_at' => null,
    ]);

    $payload = netopiaCallback($order);

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $payload)->assertOk();
    $firstPaidAt = $order->fresh()->paid_at;

    $this->post(route('payments.callback', ['gateway' => 'netopia']), $payload)->assertOk();

    expect($order->fresh()->paid_at->equalTo($firstPaidAt))->toBeTrue();
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});
