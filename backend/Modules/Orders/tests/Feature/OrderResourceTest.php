<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Filament\Resources\OrderResource;
use Modules\Orders\Filament\Resources\OrderResource\Pages\ListOrders;
use Modules\Orders\Filament\Resources\OrderResource\Pages\ViewOrder;
use Modules\Orders\Models\Order;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('registers the OrderResource on the admin panel', function () {
    expect(filament()->getPanel('admin')->getResources())->toContain(OrderResource::class);
});

it('has no create page (orders come from checkout)', function () {
    expect(array_keys(OrderResource::getPages()))->not->toContain('create');
});

it('lists orders for an admin', function () {
    $order = Order::factory()->create(['customer_name' => 'Maria Ionescu']);

    actingAs($this->admin);

    get('admin/orders')->assertOk();

    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords([$order])
        ->assertSee($order->number)
        ->assertSee('Maria Ionescu');
});

it('opens the view page for an admin', function () {
    $order = Order::factory()->has(\Modules\Orders\Models\OrderItem::factory()->count(2), 'items')->create();

    actingAs($this->admin);

    get("admin/orders/{$order->getKey()}")->assertOk();
});

it('offers only the graph-allowed targets on the status action', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Paid]);

    actingAs($this->admin);

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->callAction('changeStatus', data: ['status' => OrderStatus::Fulfilled->value]);

    expect($order->refresh()->status)->toBe(OrderStatus::Fulfilled);
});

it('rejects a status change that violates the transition graph', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Paid]);

    actingAs($this->admin);

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->callAction('changeStatus', data: ['status' => OrderStatus::Shipped->value])
        ->assertHasActionErrors(['status']);

    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
});

it('sets paid_at when the status action moves an order to paid', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'paid_at' => null]);

    actingAs($this->admin);

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->callAction('changeStatus', data: ['status' => OrderStatus::Paid->value]);

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Paid);
    expect($order->paid_at)->not->toBeNull();
});
