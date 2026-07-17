<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Livewire\CategoryShow;
use Modules\Catalog\Livewire\ProductIndex;
use Modules\Catalog\Livewire\ProductShow;

/*
|--------------------------------------------------------------------------
| Catalog storefront routes
|--------------------------------------------------------------------------
|
| Rutele publice ale magazinului. Sunt componente Livewire full-page randate
| în layout-ul de storefront din Core. Trăiesc în repo-ul companion (backend),
| deci nu intră în conflict cu rutele Statamic de pe laravel.ro.
|
| Grupul `web` este aplicat deja de RouteServiceProvider::mapWebRoutes().
|
*/

Route::get('/produse', ProductIndex::class)->name('storefront.products');
Route::get('/produse/{product:slug}', ProductShow::class)->name('storefront.product');
Route::get('/categorii/{category:slug}', CategoryShow::class)->name('storefront.category');
