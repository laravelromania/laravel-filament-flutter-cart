<?php

declare(strict_types=1);

namespace Modules\Orders\Services;

use Modules\Core\Contracts\OrderLocator;
use Modules\Core\Contracts\Payable;
use Modules\Orders\Models\Order;

/**
 * The Orders-side implementation of the Core {@see OrderLocator} contract, bound
 * in {@see \Modules\Orders\Providers\OrdersServiceProvider}. Payments resolves the
 * contract (never this class) to load the {@see Payable} it must charge, so it
 * stays free of any Orders import.
 */
class EloquentOrderLocator implements OrderLocator
{
    public function byReference(string $reference): ?Payable
    {
        return Order::where('reference', $reference)->first();
    }
}
