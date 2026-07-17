<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Orders\Models\Order;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('lists only the authenticated user\'s orders', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    Order::factory()->count(2)->create(['user_id' => $me->id]);
    Order::factory()->create(['user_id' => $other->id]);

    Sanctum::actingAs($me);

    $response = $this->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJsonStructure(['data' => [['number', 'reference', 'status' => ['value', 'label'], 'total' => ['minor', 'formatted', 'currency']]]]);

    expect($response->json('data'))->toHaveCount(2);
});

it('shows an order by number, scoped to the owner', function () {
    $me = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $me->id]);

    Sanctum::actingAs($me);

    $this->getJson("/api/v1/orders/{$order->number}")
        ->assertOk()
        ->assertJsonPath('data.number', $order->number)
        ->assertJsonPath('data.total.currency', 'RON');
});

it('404s an order that belongs to another user', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $other->id]);

    Sanctum::actingAs($me);

    $this->getJson("/api/v1/orders/{$order->number}")->assertNotFound();
});

it('rejects an unauthenticated orders request with 401', function () {
    $this->getJson('/api/v1/orders')->assertUnauthorized();
});
