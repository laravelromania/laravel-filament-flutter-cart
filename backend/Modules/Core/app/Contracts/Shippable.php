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
}
