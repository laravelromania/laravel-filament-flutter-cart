<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Checkout\Services\PaymentManager;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Orders\Models\Order;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('returns a PaymentRedirect to the internal simulator in sandbox, with no external call', function () {
    Http::fake(); // any real HTTP would be recorded

    $order = Order::factory()->create(['payment_code' => 'netopia']);

    $redirect = app(PaymentManager::class)->get('netopia')->initiate($order);

    expect($redirect)->toBeInstanceOf(PaymentRedirect::class);
    expect($redirect->method)->toBe('GET');
    expect($redirect->url)->toContain('/plati/netopia/simuleaza/');
    expect($redirect->url)->toContain($order->number);

    // The whole point of sandbox: not one byte leaves for a real gateway.
    Http::assertNothingSent();
});

it('routes the initiate entry point through the OrderLocator to the gateway redirect', function () {
    Http::fake();

    $order = Order::factory()->create(['payment_code' => 'payu']);

    $this->get(route('payments.initiate', ['reference' => $order->reference]))
        ->assertRedirect(route('payments.simulate', [
            'gateway' => 'payu',
            'reference' => $order->number,
        ]));

    Http::assertNothingSent();
});

it('404s the initiate entry point for an unknown reference', function () {
    $this->get(route('payments.initiate', ['reference' => 'does-not-exist']))
        ->assertNotFound();
});
