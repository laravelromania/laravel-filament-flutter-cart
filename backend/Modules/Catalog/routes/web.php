<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog web routes
|--------------------------------------------------------------------------
|
| În această parte, Catalog este administrat exclusiv prin panoul Filament
| (resursele Product/Category/Brand). Rutele publice ale magazinului (listare
| și pagină de produs) sosesc în Partea 5 (Storefront).
|
*/

Route::middleware(['web'])->group(function () {
    //
});
