<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Livewire\Account\Addresses;
use Modules\Customers\Livewire\Account\Dashboard;
use Modules\Customers\Livewire\Account\Orders;
use Modules\Customers\Livewire\Account\Profile;
use Modules\Customers\Livewire\Auth\Login;
use Modules\Customers\Livewire\Auth\Logout;
use Modules\Customers\Livewire\Auth\Register;

/*
|--------------------------------------------------------------------------
| Customers storefront routes
|--------------------------------------------------------------------------
|
| Autentificare storefront (guard-ul WEB implicit, împotriva tabelei `users`
| existente — fără al doilea guard, vezi articolul Partea 7) + zona "contul
| meu". Grupul `web` e deja aplicat de RouteServiceProvider::mapWebRoutes().
|
| Rutele de autentificare se numesc explicit `login`/`register`/`logout`
| (NU `storefront.login`) fiindcă middleware-urile implicite din Laravel
| (Illuminate\Auth\Middleware\Authenticate / RedirectIfAuthenticated) caută
| convențional o rută numită `login` la redirect — vezi
| CustomersServiceProvider::boot() unde legăm explicit Authenticate::redirectUsing.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/inregistrare', Register::class)->name('register');
    Route::get('/autentificare', Login::class)->name('login');
});

Route::post('/logout', Logout::class)->name('logout');

Route::middleware('auth')->prefix('cont')->name('storefront.account.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/profil', Profile::class)->name('profile');
    Route::get('/adrese', Addresses::class)->name('addresses');
    Route::get('/comenzi', Orders::class)->name('orders');
});
