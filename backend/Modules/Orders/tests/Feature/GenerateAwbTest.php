<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Core\Contracts\ShipmentService;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Filament\Resources\OrderResource\Pages\ViewOrder;
use Modules\Orders\Models\Order;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('binds the Core ShipmentService (provided by the Shipping module)', function () {
    expect(app()->bound(ShipmentService::class))->toBeTrue();
});

it('generates an AWB and moves a fulfilled order to shipped', function () {
    Http::fake();

    $order = Order::factory()->create([
        'status' => OrderStatus::Fulfilled,
        'shipping_code' => 'sameday',
        'awb' => null,
    ]);

    actingAs($this->admin);

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->callAction('generateAwb');

    $order->refresh();

    expect($order->awb)->toStartWith('SANDBOX-AWB-');
    expect($order->status)->toBe(OrderStatus::Shipped);
    Http::assertNothingSent();
});

it('hides the AWB action for an order that cannot legally transition to shipped', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending, 'awb' => null]);

    actingAs($this->admin);

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->assertActionHidden('generateAwb');
});

it('hides the AWB action once an AWB already exists', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Fulfilled,
        'awb' => 'SANDBOX-AWB-CMD-000001-42',
    ]);

    actingAs($this->admin);

    Livewire::test(ViewOrder::class, ['record' => $order->getKey()])
        ->assertActionHidden('generateAwb');
});
