<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Storefront (Core) web routes
|--------------------------------------------------------------------------
|
| Core owns the storefront shell: the home page and the shared layout. Business
| modules (Catalog, Cart, Checkout ...) register their own routes on top.
|
*/

Route::get('/', HomeController::class)->name('storefront.home');
