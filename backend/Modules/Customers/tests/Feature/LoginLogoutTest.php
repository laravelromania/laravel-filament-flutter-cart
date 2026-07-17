<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Modules\Customers\Livewire\Auth\Login as LoginComponent;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\post;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('logs an existing customer in against the default web guard and fires Login', function () {
    Event::fake([Login::class]);

    $user = User::factory()->create(['password' => Hash::make('parola123')]);

    Livewire::test(LoginComponent::class)
        ->set('email', $user->email)
        ->set('password', 'parola123')
        ->call('login')
        ->assertRedirect(route('storefront.account.dashboard'));

    assertAuthenticatedAs($user);
    Event::assertDispatched(Login::class, fn ($event) => $event->user->is($user));
});

it('rejects a wrong password and keeps the visitor a guest', function () {
    $user = User::factory()->create(['password' => Hash::make('parola123')]);

    Livewire::test(LoginComponent::class)
        ->set('email', $user->email)
        ->set('password', 'gresita')
        ->call('login')
        ->assertHasErrors(['email']);

    assertGuest();
});

it('throttles repeated failed login attempts', function () {
    $user = User::factory()->create(['password' => Hash::make('parola123')]);

    $component = Livewire::test(LoginComponent::class)
        ->set('email', $user->email)
        ->set('password', 'gresita');

    foreach (range(1, 5) as $attempt) {
        $component->call('login');
    }

    // The 6th attempt should be throttled rather than re-checked against the hash.
    $component->call('login')->assertHasErrors(['email']);

    assertGuest();
});

it('logs the customer out and redirects home', function () {
    $user = User::factory()->create();
    actingAs($user);

    post(route('logout'))->assertRedirect(route('storefront.home'));

    assertGuest();
});
