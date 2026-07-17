<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;

/**
 * A delivery method. Implemented in the Shipping module (Task 10) — FlatRate,
 * Sameday, Cargus — and resolved by code() through the ShippingManager (Task 8).
 */
interface ShippingProvider
{
    /** Stable machine code, e.g. 'flat' | 'sameday' | 'cargus'. */
    public function code(): string;

    /** Human name shown at checkout. */
    public function label(): string;

    public function quote(ShippingContext $ctx): Money;

    /** Books the shipment and returns the AWB / tracking id. */
    public function createShipment(Shippable $order): string;
}
