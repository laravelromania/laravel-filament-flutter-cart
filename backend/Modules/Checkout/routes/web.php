<?php

use Illuminate\Support\Facades\Route;
use Modules\Checkout\Livewire\Checkout;

/*
|--------------------------------------------------------------------------
| Checkout storefront routes
|--------------------------------------------------------------------------
|
| Finalizarea comenzii e o singură componentă Livewire full-page, la
| /finalizare-comanda (numele `storefront.checkout`). NU e în spatele
| middleware-ului `auth`: checkout-ul de invitat e permis — componenta decide
| singură ce colectează în funcție de starea de autentificare.
|
| Pe lângă wizard mai sunt două pagini simple: confirmarea ("mulțumim") după
| plasarea comenzii și pagina de "plată simulată" către care redirecționează
| MockPaymentGateway (folosită integral abia în Partea 11).
|
| Grupul `web` e aplicat de RouteServiceProvider::mapWebRoutes().
|
*/

Route::get('/finalizare-comanda', Checkout::class)->name('storefront.checkout');

Route::view('/finalizare-comanda/confirmare', 'checkout::confirmation')
    ->name('storefront.checkout.confirmation');

Route::view('/finalizare-comanda/plata-simulata', 'checkout::mock-payment')
    ->name('storefront.checkout.mock-payment');
