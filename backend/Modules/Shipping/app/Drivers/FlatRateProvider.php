<?php

declare(strict_types=1);

namespace Modules\Shipping\Drivers;

use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShippingProvider;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * The simplest real driver: one fixed price everywhere, whatever the weight or
 * destination. It replaces Checkout's Part-8 mock (same `flat` code) once the
 * Shipping module registers it into the manager, so nothing at checkout changes
 * shape — only the number now comes from config instead of a hard-coded literal.
 */
class FlatRateProvider implements ShippingProvider
{
    public function __construct(
        private readonly int $amountMinor = 1999,
        private readonly string $label = 'Livrare standard (tarif fix)',
    ) {
    }

    public function code(): string
    {
        return 'flat';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function quote(ShippingContext $ctx): Money
    {
        return Money::of($this->amountMinor);
    }

    public function createShipment(Shippable $order): string
    {
        // Generic drivers have no carrier API; a deterministic pseudo-AWB keeps
        // the contract honest and the flow testable.
        return 'FLAT-AWB-'.strtoupper(substr(md5($order->shippableReference()), 0, 10));
    }
}
