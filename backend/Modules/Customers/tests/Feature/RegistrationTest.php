<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Modules\Customers\Livewire\Auth\Register;
use Modules\Customers\Models\Customer;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('registers a roleless customer, creates a Customer profile, and fires Login (so Cart can merge)', function () {
    Event::fake([Login::class]);

    Livewire::test(Register::class)
        ->set('name', 'Ana Pop')
        ->set('email', 'ana@example.test')
        ->set('phone', '0722123456')
        ->set('password', 'parola123')
        ->set('password_confirmation', 'parola123')
        ->call('register')
        ->assertRedirect(route('storefront.account.dashboard'));

    $user = User::where('email', 'ana@example.test')->first();
    expect($user)->not->toBeNull();
    expect($user->hasAnyRole(['admin', 'manager']))->toBeFalse();

    $customer = Customer::where('user_id', $user->id)->first();
    expect($customer)->not->toBeNull();
    expect($customer->phone)->toBe('0722123456');

    // Cart's MergeGuestCart listener (Part 6) is registered on this exact
    // event — proving it fires (with the right user) is enough without this
    // module depending on Cart.
    Event::assertDispatched(Login::class, fn ($event) => $event->user->is($user));
});

it('blocks the freshly registered customer from /admin but lets them into /cont', function () {
    Livewire::test(Register::class)
        ->set('name', 'Bogdan Ionescu')
        ->set('email', 'bogdan@example.test')
        ->set('phone', '')
        ->set('password', 'parola123')
        ->set('password_confirmation', 'parola123')
        ->call('register');

    $user = User::where('email', 'bogdan@example.test')->first();
    expect($user->hasAnyRole(['admin', 'manager']))->toBeFalse();

    actingAs($user);

    get('/admin')->assertForbidden();
    get('/cont')->assertOk();
});

it('rejects a duplicate email on register', function () {
    User::factory()->create(['email' => 'taken@example.test']);

    Livewire::test(Register::class)
        ->set('name', 'Cineva')
        ->set('email', 'taken@example.test')
        ->set('password', 'parola123')
        ->set('password_confirmation', 'parola123')
        ->call('register')
        ->assertHasErrors(['email']);

    expect(User::where('email', 'taken@example.test')->count())->toBe(1);
});

it('requires the password confirmation to match', function () {
    Livewire::test(Register::class)
        ->set('name', 'Cineva')
        ->set('email', 'mismatch@example.test')
        ->set('password', 'parola123')
        ->set('password_confirmation', 'altaparola')
        ->call('register')
        ->assertHasErrors(['password']);

    expect(User::where('email', 'mismatch@example.test')->exists())->toBeFalse();
});
