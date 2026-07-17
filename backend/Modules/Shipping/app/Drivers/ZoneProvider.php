<?php

declare(strict_types=1);

namespace Modules\Shipping\Drivers;

use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShippingProvider;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * Prices a delivery by geographic zone: a county → zone map decides which zone
 * the destination falls in, and a zone → rate map gives the price. Counties that
 * are not listed use `defaultZone`. Deterministic — the classic "București vs.
 * rest of the country vs. hard-to-reach" tariff, expressed as data.
 */
class ZoneProvider implements ShippingProvider
{
    /**
     * @param array<string, string> $counties county => zone
     * @param array<string, int>    $rates    zone => bani
     */
    public function __construct(
        private readonly array $counties = [],
        private readonly array $rates = ['local' => 1499, 'national' => 1999, 'remote' => 2999],
        private readonly string $defaultZone = 'national',
        private readonly string $label = 'Livrare pe zone',
    ) {
    }

    public function code(): string
    {
        return 'zone';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function quote(ShippingContext $ctx): Money
    {
        $zone = $this->counties[$ctx->county] ?? $this->defaultZone;
        $bani = $this->rates[$zone] ?? $this->rates[$this->defaultZone] ?? 1999;

        return Money::of($bani);
    }

    public function createShipment(Shippable $order): string
    {
        return 'ZONE-AWB-'.strtoupper(substr(md5($order->shippableReference()), 0, 10));
    }
}
