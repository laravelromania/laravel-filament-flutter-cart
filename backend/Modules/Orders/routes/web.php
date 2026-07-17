<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\InvoiceController;
use Modules\Orders\Livewire\Account\OrderDetail;
use Modules\Orders\Livewire\Storefront\OrderConfirmation;

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

// Confirmarea comenzii e publică (checkout de invitat): componenta se apără
// singură — referința e un UUID neghicibil, iar comenzile unui client autentificat
// sunt în plus verificate pe proprietar. Aici aterizează wizard-ul după plasare,
// de aici pleacă butonul „Plătește" către modulul Payments (Partea 11).
Route::get('/comanda/{reference}', OrderConfirmation::class)
    ->name('storefront.order.confirmation');

Route::middleware('auth')->group(function () {
    Route::get('/cont/comenzi/{number}', OrderDetail::class)
        ->name('storefront.account.order');

    Route::get('/comenzi/{number}/factura', InvoiceController::class)
        ->name('orders.invoice');
});
