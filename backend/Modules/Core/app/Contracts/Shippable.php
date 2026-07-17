<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DataObjects\ShippingContext;

/**
 * Implemented by Orders\Order (Task 9). Lets a ShippingProvider quote and create
 * a shipment for "something shippable" without Core depending on Orders.
 */
interface Shippable
{
    public function shippableReference(): string;

    public function shippingContext(): ShippingContext;

    /**
     * The machine code of the shipping method chosen for this order, e.g. 'flat'
     * | 'sameday' | 'cargus'. Lets a {@see ShipmentService} pick the matching
     * {@see ShippingProvider} without Shipping ever importing the Order model.
     */
    public function shippingMethodCode(): string;
}
