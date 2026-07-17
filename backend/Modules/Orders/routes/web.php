<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\InvoiceController;
use Modules\Orders\Livewire\Account\OrderDetail;

/*
|--------------------------------------------------------------------------
| Orders storefront routes
|--------------------------------------------------------------------------
|
| Zona „contul meu" primește aici comenzile reale. Lista (`/cont/comenzi`) e
| definită de modulul Customers (Partea 7) și încorporează componenta Orders
| prin `@livewire('orders.account-orders')`; aici adăugăm doar pagina de detaliu
| a unei comenzi și descărcarea facturii.
|
| Factura e sub `auth` (nu neapărat rol): controllerul decide singur cine are
| voie — staff (rol admin/manager) pentru orice comandă, clientul doar pentru
| comenzile lui.
|
| Grupul `web` e aplicat de RouteServiceProvider::mapWebRoutes().
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/cont/comenzi/{number}', OrderDetail::class)
        ->name('storefront.account.order');

    Route::get('/comenzi/{number}/factura', InvoiceController::class)
        ->name('orders.invoice');
});
