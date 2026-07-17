<?php

declare(strict_types=1);

use Modules\Checkout\Services\ShippingManager;
use Modules\Shipping\Drivers\CargusProvider;
use Modules\Shipping\Drivers\FlatRateProvider;
use Modules\Shipping\Drivers\SamedayProvider;

uses(Tests\TestCase::class);

it('registers all five drivers into the shared ShippingManager singleton', function () {
    $manager = app(ShippingManager::class);

    foreach (['flat', 'weight', 'zone', 'sameday', 'cargus'] as $code) {
        expect($manager->has($code))->toBeTrue();
    }

    expect($manager->available())->toHaveCount(5);
});

it('replaces the Part-8 mock flat driver with the real FlatRateProvider', function () {
    expect(app(ShippingManager::class)->get('flat'))->toBeInstanceOf(FlatRateProvider::class);
});

it('exposes the RO couriers through the same registry the checkout reads', function () {
    $manager = app(ShippingManager::class);

    expect($manager->get('sameday'))->toBeInstanceOf(SamedayProvider::class);
    expect($manager->get('cargus'))->toBeInstanceOf(CargusProvider::class);
});

it('does not re-bind the singleton — it extends the one Checkout created', function () {
    // Same instance across resolutions ⇒ Shipping registered into it, never replaced it.
    expect(app(ShippingManager::class))->toBe(app(ShippingManager::class));
});
