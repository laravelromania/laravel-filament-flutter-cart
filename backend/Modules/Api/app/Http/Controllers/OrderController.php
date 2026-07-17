<?php

declare(strict_types=1);

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Api\Http\Resources\OrderResource;
use Modules\Orders\Models\Order;

/**
 * The signed-in shopper's own orders. Both endpoints are ownership-scoped to the
 * authenticated user — the list only ever contains their orders, and a lookup by
 * `number` 404s for anyone else's order, so order numbers are never enumerable
 * across accounts.
 */
class OrderController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->with('items')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, string $number): OrderResource
    {
        $order = Order::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('number', $number)
            ->with('items')
            ->firstOrFail();

        return new OrderResource($order);
    }
}
