<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Cart\Livewire\CartDrawer;
use Modules\Core\Contracts\CartRepository;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('handles the add-to-cart event, adds a line and announces cart-updated', function () {
    $product = Product::factory()->create(['name' => 'Produs Drawer']);
    $variant = ProductVariant::factory()->for($product)->create(['price' => 4500, 'stock' => 5]);

    Livewire::test(CartDrawer::class)
        ->dispatch('add-to-cart', variantId: $variant->id, qty: 2)
        ->assertDispatched('cart-updated')
        ->assertSee('Produs Drawer')
        ->assertSee('90,00 lei'); // 45,00 * 2

    expect(app(CartRepository::class)->get()->itemCount)->toBe(2);
});

it('updates a line quantity from the drawer', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

    app(CartRepository::class)->add((string) $variant->id, 1);

    Livewire::test(CartDrawer::class)
        ->call('updateQty', (string) $variant->id, 3)
        ->assertDispatched('cart-updated');

    expect(app(CartRepository::class)->get()->itemCount)->toBe(3);
});

it('removes a line from the drawer', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

    app(CartRepository::class)->add((string) $variant->id, 2);

    Livewire::test(CartDrawer::class)
        ->call('remove', (string) $variant->id)
        ->assertDispatched('cart-updated');

    expect(app(CartRepository::class)->get()->isEmpty())->toBeTrue();
});
