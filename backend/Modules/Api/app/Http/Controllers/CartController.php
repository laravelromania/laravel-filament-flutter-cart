<?php

declare(strict_types=1);

namespace Modules\Api\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Api\Http\Resources\CartResource;
use Modules\Core\Contracts\CartRepository;

/**
 * The mobile cart. Every route here is behind `auth:sanctum`, so the token'd
 * request has `Auth::check() === true` and the Cart module's container binding
 * hands back a {@see \Modules\Cart\Services\DatabaseCart} — the shopper's basket
 * is persisted per user and survives across devices and app restarts, exactly
 * like the logged-in web store. The API speaks only the Core {@see CartRepository}
 * contract; it never names a concrete cart class.
 */
class CartController
{
    public function show(CartRepository $cart): CartResource
    {
        return new CartResource($cart->get());
    }

    public function store(Request $request, CartRepository $cart): JsonResponse
    {
        $data = $request->validate([
            'variantId' => ['required', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart->add((string) $data['variantId'], (int) $data['qty']);

        return (new CartResource($cart->get()))->response()->setStatusCode(201);
    }

    public function update(Request $request, CartRepository $cart, string $variantId): CartResource
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart->update($variantId, (int) $data['qty']);

        return new CartResource($cart->get());
    }

    public function destroy(CartRepository $cart, string $variantId): CartResource
    {
        $cart->remove($variantId);

        return new CartResource($cart->get());
    }
}
