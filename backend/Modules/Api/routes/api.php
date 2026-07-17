<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Api\Http\Controllers\AuthController;
use Modules\Api\Http\Controllers\CartController;
use Modules\Api\Http\Controllers\CategoryController;
use Modules\Api\Http\Controllers\CheckoutController;
use Modules\Api\Http\Controllers\OrderController;
use Modules\Api\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API v1 (mobile)
|--------------------------------------------------------------------------
|
| Mounted by the module RouteServiceProvider with the framework `api` middleware
| group and the `/api` prefix; the `v1` segment here makes every endpoint live
| under `/api/v1/...`. `throttle:api` covers the whole surface; the credential
| endpoints get a much tighter `throttle:auth`. Protected routes require a
| Sanctum token replayed as `Authorization: Bearer <token>`.
|
*/

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    // --- Auth (public, tightly throttled) ---------------------------------
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth');

    // --- Catalog (public) --------------------------------------------------
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);

    // --- Protected (Sanctum token) ----------------------------------------
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart', [CartController::class, 'store']);
        Route::patch('cart/{variantId}', [CartController::class, 'update']);
        Route::delete('cart/{variantId}', [CartController::class, 'destroy']);

        Route::get('checkout/shipping-methods', [CheckoutController::class, 'shippingMethods']);
        Route::post('checkout', [CheckoutController::class, 'store']);

        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{number}', [OrderController::class, 'show']);
    });
});
