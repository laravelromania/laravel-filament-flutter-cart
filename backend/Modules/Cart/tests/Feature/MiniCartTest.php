<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Cart\Livewire\MiniCart;
use Modules\Core\Contracts\CartRepository;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('shows the item count on mount', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

    app(CartRepository::class)->add((string) $variant->id, 3);

    Livewire::test(MiniCart::class)
        ->assertSet('itemCount', 3)
        ->assertSee('3');
});

it('refreshes the badge when cart-updated is dispatched', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

    $component = Livewire::test(MiniCart::class)->assertSet('itemCount', 0);

    app(CartRepository::class)->add((string) $variant->id, 5);

    $component->dispatch('cart-updated')->assertSet('itemCount', 5);
});
