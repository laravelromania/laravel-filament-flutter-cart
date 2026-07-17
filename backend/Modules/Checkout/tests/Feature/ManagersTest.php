<?php

declare(strict_types=1);

use Modules\Checkout\Drivers\MockPaymentGateway;
use Modules\Checkout\Drivers\MockShippingProvider;
use Modules\Checkout\Services\PaymentManager;
use Modules\Checkout\Services\ShippingManager;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

uses(Tests\TestCase::class);

it('resolves the mock shipping driver through the ShippingManager', function () {
    $manager = app(ShippingManager::class);

    expect($manager->available())->toHaveCount(1);
    expect($manager->get('flat'))->toBeInstanceOf(MockShippingProvider::class);
});

it('resolves the mock payment driver through the PaymentManager', function () {
    $manager = app(PaymentManager::class);

    expect($manager->available())->toHaveCount(1);
    expect($manager->get('mock'))->toBeInstanceOf(MockPaymentGateway::class);
});

it('throws when asked for an unknown shipping code', function () {
    app(ShippingManager::class)->get('does-not-exist');
})->throws(InvalidArgumentException::class);

it('quotes a fixed flat rate regardless of destination', function () {
    $ctx = new ShippingContext('Cluj', 'Cluj-Napoca', '400114', 1.0, Money::of(15000));

    $quote = app(ShippingManager::class)->get('flat')->quote($ctx);

    expect($quote->getMinorAmount())->toBe(1999);
});
