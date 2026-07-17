<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Orders\Models\Order;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('lets an admin download an order invoice as a PDF', function () {
    Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $order = Order::factory()->has(\Modules\Orders\Models\OrderItem::factory()->count(2), 'items')->create();

    actingAs($admin);

    $response = get(route('orders.invoice', ['number' => $order->number]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

it('lets the owner download their own invoice', function () {
    $user = User::factory()->create();
    $order = Order::factory()->has(\Modules\Orders\Models\OrderItem::factory(), 'items')->create(['user_id' => $user->id]);

    actingAs($user);

    get(route('orders.invoice', ['number' => $order->number]))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('forbids a different signed-in shopper from another order invoice', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    actingAs($intruder);

    get(route('orders.invoice', ['number' => $order->number]))->assertForbidden();
});

it('redirects a guest to login', function () {
    $order = Order::factory()->create();

    get(route('orders.invoice', ['number' => $order->number]))->assertRedirect(route('login'));
});
