<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\Shippable;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;
use Modules\Shipping\Drivers\CargusProvider;
use Modules\Shipping\Drivers\SamedayProvider;

uses(Tests\TestCase::class);

/** A Shippable stand-in so we never import the Orders module in Shipping's tests. */
function sandboxOrder(string $ref = 'CMD-000123', string $code = 'sameday', float $weightKg = 2.0): Shippable
{
    return new class($ref, $code, $weightKg) implements Shippable
    {
        public function __construct(private string $ref, private string $code, private float $weightKg)
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
            return new ShippingContext('Cluj', 'Cluj-Napoca', '400114', $this->weightKg, Money::of(15000));
        }
    };
}

it('runs Sameday in sandbox when no credentials are configured', function () {
    expect((new SamedayProvider('', '', true))->isSandbox())->toBeTrue();
    expect((new SamedayProvider('', '', false))->isSandbox())->toBeTrue(); // missing creds still forces sandbox
});

it('Sameday sandbox quotes deterministically and books an AWB without any HTTP call', function () {
    Http::fake();

    $sameday = new SamedayProvider('', '', true);
    $ctx = new ShippingContext('Cluj', 'Cluj-Napoca', '400114', 2.0, Money::of(15000));

    expect($sameday->quote($ctx)->getMinorAmount())->toBe(1990 + 2 * 500); // 2990: deterministic

    $awb = $sameday->createShipment(sandboxOrder('CMD-000777'));
    expect($awb)->toStartWith('SANDBOX-AWB-CMD-000777-');

    // The whole point of sandbox: not a single real request left the app.
    Http::assertNothingSent();
});

it('forces Cargus into sandbox when the subscription key is missing, even with sandbox=false', function () {
    expect((new CargusProvider('', '', '', false))->isSandbox())->toBeTrue();
    expect((new CargusProvider('key', 'user', 'pass', true))->isSandbox())->toBeTrue();
});

it('Cargus sandbox quotes deterministically and books an AWB without any HTTP call', function () {
    Http::fake();

    $cargus = new CargusProvider('', '', '', true);
    $ctx = new ShippingContext('Cluj', 'Cluj-Napoca', '400114', 3.0, Money::of(15000));

    expect($cargus->quote($ctx)->getMinorAmount())->toBe(2290 + 3 * 450); // 3640: deterministic

    $awb = $cargus->createShipment(sandboxOrder('CMD-000888', 'cargus'));
    expect($awb)->toStartWith('SANDBOX-AWB-CMD-000888-');

    Http::assertNothingSent();
});
