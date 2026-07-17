<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Customers\Livewire\Account\Addresses;
use Modules\Customers\Models\Address;
use Modules\Customers\Models\Customer;

use function Pest\Laravel\actingAs;

uses(Tests\TestCase::class, RefreshDatabase::class);

function fillAddressForm($test, array $overrides = [])
{
    $data = array_merge([
        'form.type' => 'shipping',
        'form.name' => 'Ana Pop',
        'form.phone' => '0722123456',
        'form.county' => 'Cluj',
        'form.city' => 'Cluj-Napoca',
        'form.street' => 'Str. Memorandumului 10',
        'form.postal_code' => '400114',
    ], $overrides);

    foreach ($data as $key => $value) {
        $test->set($key, $value);
    }

    return $test;
}

it('creates an address for the current customer, lazily creating the Customer profile', function () {
    $user = User::factory()->create();
    actingAs($user);

    expect(Customer::where('user_id', $user->id)->exists())->toBeFalse();

    $test = Livewire::test(Addresses::class)->call('create');
    fillAddressForm($test)->call('save');

    $customer = Customer::where('user_id', $user->id)->firstOrFail();
    expect($customer->addresses)->toHaveCount(1);

    $address = $customer->addresses->first();
    expect($address->name)->toBe('Ana Pop');
    expect($address->city)->toBe('Cluj-Napoca');
    expect($address->type)->toBe('shipping');
});

it('edits an existing address', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $address = Address::factory()->for($customer)->create(['city' => 'Vechi Oraș']);

    actingAs($user);

    Livewire::test(Addresses::class)
        ->call('edit', $address->id)
        ->set('form.city', 'Oraș Nou')
        ->call('save');

    expect($address->fresh()->city)->toBe('Oraș Nou');
    expect(Address::count())->toBe(1);
});

it('deletes an address', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $address = Address::factory()->for($customer)->create();

    actingAs($user);

    Livewire::test(Addresses::class)->call('delete', $address->id);

    expect(Address::find($address->id))->toBeNull();
});

it('sets an address as default and clears the previous default', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $first = Address::factory()->for($customer)->create(['is_default' => true]);
    $second = Address::factory()->for($customer)->create(['is_default' => false]);

    actingAs($user);

    Livewire::test(Addresses::class)->call('setDefault', $second->id);

    expect($first->fresh()->is_default)->toBeFalse();
    expect($second->fresh()->is_default)->toBeTrue();
});

it('unsets the previous default when saving a new address as default', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    $existing = Address::factory()->for($customer)->create(['is_default' => true]);

    actingAs($user);

    $test = Livewire::test(Addresses::class)->call('create');
    fillAddressForm($test, ['form.is_default' => true])->call('save');

    expect($existing->fresh()->is_default)->toBeFalse();
    expect($customer->addresses()->where('is_default', true)->count())->toBe(1);
});

it('validates required address fields', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test(Addresses::class)
        ->call('create')
        ->set('form.name', '')
        ->call('save')
        ->assertHasErrors(['form.name']);
});

it('only lists addresses belonging to the current customer', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create(['user_id' => $user->id]);
    Address::factory()->for($customer)->create(['city' => 'Orașul Meu']);

    $otherCustomer = Customer::factory()->create();
    Address::factory()->for($otherCustomer)->create(['city' => 'Orașul Altcuiva']);

    actingAs($user);

    Livewire::test(Addresses::class)
        ->assertSee('Orașul Meu')
        ->assertDontSee('Orașul Altcuiva');
});
