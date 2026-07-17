<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Cart\Services\DatabaseCart;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Core\Contracts\CartRepository;

uses(Tests\TestCase::class, RefreshDatabase::class);

/** A product with one variant priced at 75,00 lei. */
function apiVariant(int $priceMinor = 7500): ProductVariant
{
    $product = Product::factory()->create(['price' => $priceMinor]);

    return ProductVariant::factory()->for($product)->create(['price' => null, 'stock' => 10]);
}

it('resolves the DatabaseCart for a token-authenticated request', function () {
    Sanctum::actingAs(User::factory()->create());

    expect(app(CartRepository::class))->toBeInstanceOf(DatabaseCart::class);
});

it('adds a variant to the cart and returns it in the Money JSON shape', function () {
    Sanctum::actingAs($user = User::factory()->create());
    $variant = apiVariant(7500);

    $add = $this->postJson('/api/v1/cart', ['variantId' => $variant->id, 'qty' => 2]);

    $add->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'item_count',
                'subtotal' => ['minor', 'formatted', 'currency'],
                'lines' => [['variant_id', 'name', 'quantity', 'unit_price' => ['minor', 'formatted', 'currency'], 'line_total' => ['minor', 'formatted', 'currency']]],
            ],
        ])
        ->assertJsonPath('data.item_count', 2)
        ->assertJsonPath('data.subtotal.minor', 15000)
        ->assertJsonPath('data.subtotal.formatted', '150,00 lei')
        ->assertJsonPath('data.lines.0.unit_price.minor', 7500)
        ->assertJsonPath('data.lines.0.quantity', 2);

    // Proof the DatabaseCart (not the session cart) handled it: a row in the DB.
    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('cart_items', ['variant_id' => $variant->id, 'qty' => 2]);
});

it('reads the cart back with GET', function () {
    Sanctum::actingAs(User::factory()->create());
    $variant = apiVariant(7500);

    $this->postJson('/api/v1/cart', ['variantId' => $variant->id, 'qty' => 1])->assertCreated();

    $this->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.item_count', 1)
        ->assertJsonPath('data.subtotal.minor', 7500);
});

it('updates and removes a cart line', function () {
    Sanctum::actingAs(User::factory()->create());
    $variant = apiVariant(7500);

    $this->postJson('/api/v1/cart', ['variantId' => $variant->id, 'qty' => 1])->assertCreated();

    $this->patchJson("/api/v1/cart/{$variant->id}", ['qty' => 3])
        ->assertOk()
        ->assertJsonPath('data.item_count', 3);

    $this->deleteJson("/api/v1/cart/{$variant->id}")
        ->assertOk()
        ->assertJsonPath('data.item_count', 0);
});

it('rejects adding a non-existent variant as 422', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/cart', ['variantId' => 999999, 'qty' => 1])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['variantId']]);
});

it('rejects an unauthenticated cart read with 401', function () {
    $this->getJson('/api/v1/cart')->assertUnauthorized();
});
