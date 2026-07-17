<?php

declare(strict_types=1);

namespace Modules\Shipping\Services;

use Modules\Checkout\Services\ShippingManager;
use Modules\Core\Contracts\Shippable;
use Modules\Core\Contracts\ShipmentService;

/**
 * The Shipping module's implementation of the Core {@see ShipmentService}
 * contract. It looks up the driver the order was shipped with — by the order's
 * own {@see Shippable::shippingMethodCode()} — through the same
 * {@see ShippingManager} registry the checkout uses, and asks it to book the
 * shipment.
 *
 * This is the one place Shipping touches Checkout (resolving the manager, the
 * agreed extension seam). It never imports the Orders module: it only ever sees
 * an order through the Core {@see Shippable} interface.
 */
class CourierShipmentService implements ShipmentService
{
    public function __construct(private readonly ShippingManager $manager)
    {
    }

    public function createFor(Shippable $order): string
    {
        return $this->manager
            ->get($order->shippingMethodCode())
            ->createShipment($order);
    }
}
