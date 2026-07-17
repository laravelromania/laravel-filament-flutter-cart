<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

/**
 * Books a real shipment for a {@see Shippable} order and returns its AWB /
 * tracking id.
 *
 * The seam that keeps Orders → Core only. Orders (Part 9) resolves THIS contract
 * to generate an AWB from its admin panel; the Shipping module (Part 10) binds
 * the concrete {@see \Modules\Shipping\Services\CourierShipmentService}. When
 * Shipping is disabled the binding is absent, so Orders guards with
 * `app()->bound(ShipmentService::class)` and simply hides the action.
 */
interface ShipmentService
{
    /** Creates the shipment for the order and returns the AWB / tracking id. */
    public function createFor(Shippable $order): string;
}
