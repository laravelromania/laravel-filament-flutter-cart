<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cart\Services\DatabaseCart;
use Modules\Cart\Services\SessionCart;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('merges the guest session cart into the user database cart on login and clears the session', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 2000, 'stock' => 5]);

    // Guest adds a variant to the session cart.
    $session = app(SessionCart::class);
    $session->add((string) $variant->id, 2);
    expect($session->get()->itemCount)->toBe(2);

    // Logging in fires Illuminate\Auth\Events\Login -> MergeGuestCart listener.
    $user = User::factory()->create();
    auth()->login($user);

    // Session cart is emptied.
    expect(app(SessionCart::class)->get()->isEmpty())->toBeTrue();

    // Items now live in the user's database cart.
    $db = new DatabaseCart((int) $user->id);
    expect($db->get()->itemCount)->toBe(2);
    $this->assertDatabaseHas('cart_items', ['variant_id' => $variant->id, 'qty' => 2]);
});

it('adds guest quantities on top of an existing database cart on login', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 1500, 'stock' => 20]);

    $user = User::factory()->create();

    // User already had 1 in their DB cart (from a previous session).
    (new DatabaseCart((int) $user->id))->add((string) $variant->id, 1);

    // As a guest now, they add 2 more to the session cart.
    app(SessionCart::class)->add((string) $variant->id, 2);

    auth()->login($user);

    expect((new DatabaseCart((int) $user->id))->get()->itemCount)->toBe(3);
    $this->assertDatabaseHas('cart_items', ['variant_id' => $variant->id, 'qty' => 3]);
});

it('does nothing when the guest session cart is empty on login', function () {
    $user = User::factory()->create();

    auth()->login($user);

    expect((new DatabaseCart((int) $user->id))->get()->isEmpty())->toBeTrue();
    $this->assertDatabaseCount('cart_items', 0);
});
