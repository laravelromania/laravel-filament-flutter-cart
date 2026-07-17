<?php

declare(strict_types=1);

namespace Modules\Shipping\Drivers;

use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShippingProvider;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * Prices a delivery from a table of weight tiers: `[maxKg => bani]`. The quote is
 * the rate of the first tier whose upper bound the parcel's weight does not
 * exceed; anything heavier than the last tier falls back to a single flat
 * `fallback` rate. Fully deterministic — no carrier involved.
 */
class WeightBasedProvider implements ShippingProvider
{
    /** @var array<int|float, int> maxKg => bani, ascending */
    private readonly array $tiers;

    /**
     * @param array<int|float, int> $tiers maxKg => bani
     */
    public function __construct(
        array $tiers = [1 => 1500, 5 => 2500, 30 => 4000],
        private readonly int $fallback = 6000,
        private readonly string $label = 'Livrare în funcție de greutate',
    ) {
        ksort($tiers);
        $this->tiers = $tiers;
    }

    public function code(): string
    {
        return 'weight';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function quote(ShippingContext $ctx): Money
    {
        foreach ($this->tiers as $maxKg => $bani) {
            if ($ctx->weightKg <= $maxKg) {
                return Money::of($bani);
            }
        }

        return Money::of($this->fallback);
    }

    public function createShipment(Shippable $order): string
    {
        return 'WEIGHT-AWB-'.strtoupper(substr(md5($order->shippableReference()), 0, 10));
    }
}
