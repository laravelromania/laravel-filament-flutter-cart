<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShipmentService;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

uses(Tests\TestCase::class);

/** A Shippable stand-in, so the ShipmentService test needs no Orders model. */
function shipmentOrder(string $ref, string $code): Shippable
{
    return new class($ref, $code) implements Shippable
    {
        public function __construct(private string $ref, private string $code)
        {
        }

        public function shippableReference(): string
        {
            return $this->ref;
        }

        public function shippingMethodCode(): string
        {
            return $this->code;
        }

        public function shippingContext(): ShippingContext
        {
            return new ShippingContext('Cluj', 'Cluj-Napoca', '400114', 1.5, Money::of(15000));
        }
    };
}

it('binds the Core ShipmentService contract to the Shipping implementation', function () {
    expect(app()->bound(ShipmentService::class))->toBeTrue();
});

it('createFor picks the driver by the order shipping method code and returns an AWB', function () {
    Http::fake();

    $awb = app(ShipmentService::class)->createFor(shipmentOrder('CMD-000999', 'sameday'));

    expect($awb)->toStartWith('SANDBOX-AWB-CMD-000999-');
    Http::assertNothingSent();
});

it('routes a flat order to the flat driver AWB scheme', function () {
    $awb = app(ShipmentService::class)->createFor(shipmentOrder('CMD-000111', 'flat'));

    expect($awb)->toStartWith('FLAT-AWB-');
});
