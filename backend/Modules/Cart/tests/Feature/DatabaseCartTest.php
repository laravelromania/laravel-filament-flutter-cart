<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cart\Services\DatabaseCart;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('persists the cart in the database for an authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 3000, 'stock' => 5]);

    $cart = app(DatabaseCart::class);
    $cart->add((string) $variant->id, 2);
    $cart->add((string) $variant->id, 1);

    $data = $cart->get();

    expect($data->itemCount)->toBe(3)
        ->and($data->lines)->toHaveCount(1)
        ->and($data->subtotal->getMinorAmount())->toBe(9000);

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('cart_items', ['variant_id' => $variant->id, 'qty' => 3]);
});

it('updates and removes rows for an authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 2500, 'stock' => 20]);

    $cart = app(DatabaseCart::class);
    $cart->add((string) $variant->id, 5);

    $cart->update((string) $variant->id, 2);
    expect($cart->get()->itemCount)->toBe(2);
    $this->assertDatabaseHas('cart_items', ['variant_id' => $variant->id, 'qty' => 2]);

    $cart->remove((string) $variant->id);
    expect($cart->get()->isEmpty())->toBeTrue();
    $this->assertDatabaseMissing('cart_items', ['variant_id' => $variant->id]);
});

it('keeps a single cart row per user (user_id is unique)', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 5]);

    app(DatabaseCart::class)->add((string) $variant->id, 1);
    app(DatabaseCart::class)->add((string) $variant->id, 1);

    expect(\Modules\Cart\Models\Cart::query()->where('user_id', $user->id)->count())->toBe(1);
});
