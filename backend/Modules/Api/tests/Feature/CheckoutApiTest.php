<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Core\Contracts\CartRepository;

uses(Tests\TestCase::class, RefreshDatabase::class);

/** Seed one variant at 75,00 lei and drop $qty into the authenticated user's DB cart. */
function seedApiCart(int $qty = 2): ProductVariant
{
    $product = Product::factory()->create(['name' => 'Produs API', 'price' => 7500]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => null, 'stock' => 10]);

    app(CartRepository::class)->add((string) $variant->id, $qty);

    return $variant;
}

/** A valid Romanian address payload. */
function apiAddress(): array
{
    return [
        'name' => 'Ion Popescu',
        'phone' => '0712345678',
        'county' => 'Cluj',
        'city' => 'Cluj-Napoca',
        'street' => 'Str. Memorandumului 1',
        'postal_code' => '400114',
    ];
}

it('lists shipping methods with quotes for the cart', function () {
    Sanctum::actingAs(User::factory()->create());
    seedApiCart(2);

    $response = $this->getJson('/api/v1/checkout/shipping-methods');

    $response->assertOk()
        ->assertJsonStructure(['data' => [['code', 'label', 'cost' => ['minor', 'formatted', 'currency']]]]);

    expect(collect($response->json('data'))->pluck('code'))->toContain('flat');
});

it('places an order through the API and clears the cart', function () {
    Sanctum::actingAs($user = User::factory()->create(['email' => 'buyer@example.com', 'name' => 'Buyer One']));
    seedApiCart(2); // 2 x 75,00 = 150,00 lei

    $response = $this->postJson('/api/v1/checkout', [
        'billing' => apiAddress(),
        'shipping' => apiAddress(),
        'shippingCode' => 'flat',
        'paymentCode' => 'mock',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'number', 'reference',
                'status' => ['value', 'label'],
                'items_subtotal' => ['minor', 'formatted', 'currency'],
                'shipping_total' => ['minor', 'formatted', 'currency'],
                'total' => ['minor', 'formatted', 'currency'],
                'items' => [['name', 'quantity', 'unit_price' => ['minor'], 'line_total' => ['minor']]],
            ],
            'payment',
        ])
        ->assertJsonPath('data.items_subtotal.minor', 15000)
        ->assertJsonPath('data.status.value', 'pending');

    // total == items_subtotal + shipping_total (whatever the flat quote is).
    $subtotal = $response->json('data.items_subtotal.minor');
    $shipping = $response->json('data.shipping_total.minor');
    expect($response->json('data.total.minor'))->toBe($subtotal + $shipping);

    // A real order row, owned by the buyer.
    $this->assertDatabaseHas('orders', [
        'reference' => $response->json('data.reference'),
        'user_id' => $user->id,
        'email' => 'buyer@example.com',
        'items_subtotal' => 15000,
    ]);

    // The cart was cleared.
    $this->assertDatabaseMissing('cart_items', ['variant_id' => $response->json('data.items.0.variant_id')]);
    $this->getJson('/api/v1/cart')->assertJsonPath('data.item_count', 0);

    // COD/mock orders carry no online-payment redirect.
    expect($response->json('payment'))->toBeNull();
});

it('rejects checkout with an empty cart as 422', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/checkout', [
        'billing' => apiAddress(),
        'shipping' => apiAddress(),
        'shippingCode' => 'flat',
        'paymentCode' => 'mock',
    ])->assertStatus(422);
});

it('rejects checkout with an unknown shipping code as 422', function () {
    Sanctum::actingAs(User::factory()->create());
    seedApiCart(1);

    $this->postJson('/api/v1/checkout', [
        'billing' => apiAddress(),
        'shipping' => apiAddress(),
        'shippingCode' => 'teleportare',
        'paymentCode' => 'mock',
    ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['shippingCode']]);
});
