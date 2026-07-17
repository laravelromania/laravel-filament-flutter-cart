<?php

declare(strict_types=1);

use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;
use Modules\Shipping\Drivers\FlatRateProvider;
use Modules\Shipping\Drivers\WeightBasedProvider;
use Modules\Shipping\Drivers\ZoneProvider;

/**
 * Pure, framework-free unit tests: the generic drivers are plain objects, so we
 * build them with explicit config and assert their quote() maths is fully
 * deterministic — no container, no config, no network.
 */
function shipCtx(float $weightKg = 1.0, string $county = 'Cluj'): ShippingContext
{
    return new ShippingContext($county, 'Oraș', '400000', $weightKg, Money::of(15000));
}

it('flat rate quotes the configured fixed amount, whatever the weight or destination', function () {
    $flat = new FlatRateProvider(1999, 'Tarif fix');

    expect($flat->code())->toBe('flat');
    expect($flat->quote(shipCtx(0.5))->getMinorAmount())->toBe(1999);
    expect($flat->quote(shipCtx(25.0, 'Tulcea'))->getMinorAmount())->toBe(1999);
});

it('weight based quotes the first tier not exceeded, with a fallback for heavy parcels', function () {
    $weight = new WeightBasedProvider([1 => 1500, 5 => 2500, 30 => 4000], 6000);

    expect($weight->code())->toBe('weight');
    expect($weight->quote(shipCtx(0.8))->getMinorAmount())->toBe(1500);
    expect($weight->quote(shipCtx(1.0))->getMinorAmount())->toBe(1500);
    expect($weight->quote(shipCtx(3.0))->getMinorAmount())->toBe(2500);
    expect($weight->quote(shipCtx(10.0))->getMinorAmount())->toBe(4000);
    expect($weight->quote(shipCtx(50.0))->getMinorAmount())->toBe(6000);
});

it('zone based quotes by county → zone → rate, defaulting unlisted counties', function () {
    $zone = new ZoneProvider(
        ['București' => 'local', 'Tulcea' => 'remote'],
        ['local' => 1499, 'national' => 1999, 'remote' => 2999],
        'national',
    );

    expect($zone->code())->toBe('zone');
    expect($zone->quote(shipCtx(1.0, 'București'))->getMinorAmount())->toBe(1499);
    expect($zone->quote(shipCtx(1.0, 'Tulcea'))->getMinorAmount())->toBe(2999);
    expect($zone->quote(shipCtx(1.0, 'Județ necunoscut'))->getMinorAmount())->toBe(1999);
});
