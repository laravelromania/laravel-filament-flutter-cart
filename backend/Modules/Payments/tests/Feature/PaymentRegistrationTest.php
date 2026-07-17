<?php

declare(strict_types=1);

use Modules\Checkout\Services\PaymentManager;
use Modules\Payments\Drivers\NetopiaProvider;
use Modules\Payments\Drivers\PayuProvider;

uses(Tests\TestCase::class);

it('registers the Netopia and PayU drivers into the shared PaymentManager singleton', function () {
    $manager = app(PaymentManager::class);

    expect($manager->has('netopia'))->toBeTrue();
    expect($manager->has('payu'))->toBeTrue();

    // Part 8 pre-loaded only the mock; Payments adds two real gateways to it.
    expect($manager->available())->toHaveCount(3);
});

it('resolves the concrete driver classes by code', function () {
    $manager = app(PaymentManager::class);

    expect($manager->get('netopia'))->toBeInstanceOf(NetopiaProvider::class);
    expect($manager->get('payu'))->toBeInstanceOf(PayuProvider::class);
});

it('does not re-bind the manager — it extends the one Checkout created', function () {
    expect(app(PaymentManager::class))->toBe(app(PaymentManager::class));
});

it('defaults to sandbox when no merchant credentials are configured', function () {
    $manager = app(PaymentManager::class);

    expect($manager->get('netopia')->isSandbox())->toBeTrue();
    expect($manager->get('payu')->isSandbox())->toBeTrue();
});
