<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('redirects a guest to the login page for every /cont route', function (string $path) {
    get($path)->assertRedirect(route('login'));
})->with([
    '/cont',
    '/cont/profil',
    '/cont/adrese',
    '/cont/comenzi',
]);

it('lets an authenticated user into every /cont route', function (string $path) {
    actingAs(User::factory()->create());

    get($path)->assertOk();
})->with([
    '/cont',
    '/cont/profil',
    '/cont/adrese',
    '/cont/comenzi',
]);

it('serves the register and login pages to guests', function () {
    get('/inregistrare')->assertOk();
    get('/autentificare')->assertOk();
});
