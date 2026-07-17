<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog API routes
|--------------------------------------------------------------------------
|
| API-ul public al catalogului (produse pentru aplicația mobilă) este construit
| în Partea 12. Deocamdată nu expunem rute API din acest modul.
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    //
});
