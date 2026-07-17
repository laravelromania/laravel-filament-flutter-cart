<?php

declare(strict_types=1);

namespace Modules\Checkout\Drivers;

use Illuminate\Support\Str;
use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShippingProvider;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * A stand-in courier used while the store is being built. It implements the very
 * same {@see ShippingProvider} contract the real carriers will (Part 10), so the
 * Checkout flow, the summary totals and the tests are all written against the
 * finished shape — only the numbers are fake: a flat 19,99 lei everywhere and a
 * made-up AWB.
 */
class MockShippingProvider implements ShippingProvider
{
    public function code(): string
    {
        return 'flat';
    }

    public function label(): string
    {
        return 'Livrare standard prin curier';
    }

    public function quote(ShippingContext $ctx): Money
    {
        return Money::of(1999); // 19,99 lei, flat, regardless of destination or weight
    }

    public function createShipment(Shippable $order): string
    {
        return 'MOCK-AWB-'.Str::upper(Str::random(10));
    }
}
