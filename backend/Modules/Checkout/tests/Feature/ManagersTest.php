<?php

declare(strict_types=1);

use Modules\Checkout\Drivers\MockPaymentGateway;
use Modules\Checkout\Services\PaymentManager;
use Modules\Checkout\Services\ShippingManager;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

uses(Tests\TestCase::class);

it('exposes the drivers the Shipping module (Part 10) registers into the ShippingManager', function () {
    $manager = app(ShippingManager::class);

    // Part 8 pre-loads only a mock 'flat'. Part 10's Shipping module resolves this
    // same singleton in its boot() and registers the real drivers: it replaces the
    // 'flat' placeholder and adds weight/zone/sameday/cargus, so they appear at
    // checkout without Checkout ever depending on the Shipping module.
    foreach (['flat', 'weight', 'zone', 'sameday', 'cargus'] as $code) {
        expect($manager->has($code))->toBeTrue();
    }

    expect($manager->available())->toHaveCount(5);
});

it('resolves the mock payment driver through the PaymentManager', function () {
    $manager = app(PaymentManager::class);

    // Part 8 pre-loads only the mock gateway. Part 11's Payments module resolves
    // this same singleton in its boot() and registers Netopia + PayU into it, so
    // three methods are now offered at checkout without Checkout depending on
    // Payments. The mock stays as the offline test placeholder.
    expect($manager->available())->toHaveCount(3);
    expect($manager->get('mock'))->toBeInstanceOf(MockPaymentGateway::class);
    expect($manager->has('netopia'))->toBeTrue();
    expect($manager->has('payu'))->toBeTrue();
});

it('throws when asked for an unknown shipping code', function () {
    app(ShippingManager::class)->get('does-not-exist');
})->throws(InvalidArgumentException::class);

it('quotes a fixed flat rate regardless of destination', function () {
    $ctx = new ShippingContext('Cluj', 'Cluj-Napoca', '400114', 1.0, Money::of(15000));

    $quote = app(ShippingManager::class)->get('flat')->quote($ctx);

    expect($quote->getMinorAmount())->toBe(1999);
});
