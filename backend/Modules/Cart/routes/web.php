<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Livewire\CartPage;

/*
|--------------------------------------------------------------------------
| Cart storefront routes
|--------------------------------------------------------------------------
|
| Coșul are o singură pagină publică: coșul complet, la /cos. E o componentă
| Livewire full-page randată în layout-ul de storefront din Core. Drawer-ul și
| mini-cart-ul din antet nu au rute proprii — trăiesc în layout.
|
| Grupul `web` e aplicat de RouteServiceProvider::mapWebRoutes().
|
*/

Route::get('/cos', CartPage::class)->name('storefront.cart');
