<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Orders\Livewire\Account\OrderHistory;
use Modules\Orders\Models\Order;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('registers the account order Livewire components by name', function () {
    $registry = app(\Livewire\Mechanisms\ComponentRegistry::class);

    expect($registry->getClass('orders.account-orders'))->toBe(OrderHistory::class);
    expect($registry->getClass('orders.account-order'))->toBe(\Modules\Orders\Livewire\Account\OrderDetail::class);
});

it('lists only the signed-in shopper own orders', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Order::factory()->create(['user_id' => $user->id, 'customer_name' => 'A Mea']);
    $theirs = Order::factory()->create(['user_id' => $other->id, 'customer_name' => 'A Altcuiva']);

    actingAs($user);

    Livewire::test(OrderHistory::class)
        ->assertSee($mine->number)
        ->assertDontSee($theirs->number);
});

it('embeds the order history on the Customers /cont/comenzi page', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    actingAs($user);

    get('/cont/comenzi')
        ->assertOk()
        ->assertSee($order->number);
});

it('shows a single order to its owner with an invoice link', function () {
    $user = User::factory()->create();
    $order = Order::factory()->has(\Modules\Orders\Models\OrderItem::factory(), 'items')->create(['user_id' => $user->id]);

    actingAs($user);

    get(route('storefront.account.order', ['number' => $order->number]))
        ->assertOk()
        ->assertSee($order->number)
        ->assertSee(route('orders.invoice', ['number' => $order->number]));
});

it('forbids viewing another shopper order', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    actingAs($intruder);

    get(route('storefront.account.order', ['number' => $order->number]))->assertForbidden();
});

it('redirects a guest away from a single order view', function () {
    $order = Order::factory()->create();

    get(route('storefront.account.order', ['number' => $order->number]))->assertRedirect(route('login'));
});
