<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Catalog\Livewire\ProductShow;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('renders the product name and the default variant price', function () {
    $product = Product::factory()->create(['name' => 'Produs Detaliu', 'price' => 12990]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => 9990, 'stock' => 3]);

    Livewire::test(ProductShow::class, ['product' => $product])
        ->assertSee('Produs Detaliu')
        ->assertSee($variant->effectivePrice()->format()); // "99,90 lei"
});

it('updates the displayed price when a different variant is selected', function () {
    $product = Product::factory()->create(['price' => 12990]);
    $cheap = ProductVariant::factory()->for($product)->create(['price' => 4990, 'stock' => 5]);
    $pricey = ProductVariant::factory()->for($product)->create(['price' => 19990, 'stock' => 5]);

    Livewire::test(ProductShow::class, ['product' => $product])
        ->call('selectVariant', $pricey->id)
        ->assertSee($pricey->effectivePrice()->format())
        ->call('selectVariant', $cheap->id)
        ->assertSee($cheap->effectivePrice()->format());
});

it('dispatches the add-to-cart event with the selected variant and quantity', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    Livewire::test(ProductShow::class, ['product' => $product])
        ->call('addToCart', $variant->id)
        ->assertDispatched('add-to-cart', variantId: $variant->id, qty: 1);
});

it('aborts with 404 for an inactive product', function () {
    $product = Product::factory()->create(['is_active' => false]);

    Livewire::test(ProductShow::class, ['product' => $product])
        ->assertStatus(404);
});
