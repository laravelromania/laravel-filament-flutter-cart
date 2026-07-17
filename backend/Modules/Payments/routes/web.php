<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Payments storefront routes
|--------------------------------------------------------------------------
|
| Fluxul de plată, fără cuplaj cu Orders: pagina de confirmare (modulul Orders)
| trimite aici prin numele de rută `payments.initiate`; aici încărcăm comanda
| prin contractul Core OrderLocator și o predăm gateway-ului.
|
| Ruta de callback (IPN) e scutită de CSRF în bootstrap/app.php — un gateway care
| notifică server-to-server nu are token de sesiune. Semnătura callback-ului e
| verificată în controller ÎNAINTE de a avea încredere în el.
|
*/

Route::get('/plati/initiaza/{reference}', [PaymentController::class, 'initiate'])
    ->name('payments.initiate');

Route::post('/plati/{gateway}/callback', [PaymentController::class, 'callback'])
    ->name('payments.callback');

Route::get('/plati/{gateway}/return/{reference}', [PaymentController::class, 'returnFromGateway'])
    ->name('payments.return');

Route::match(['get', 'post'], '/plati/{gateway}/simuleaza/{reference}', [PaymentController::class, 'simulate'])
    ->name('payments.simulate');
