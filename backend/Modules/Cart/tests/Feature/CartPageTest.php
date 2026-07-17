<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Cart\Livewire\CartPage;
use Modules\Core\Contracts\CartRepository;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('serves the /cos route with the CartPage component', function () {
    $this->get('/cos')
        ->assertOk()
        ->assertSeeLivewire(CartPage::class);
});

it('renders the cart lines and the subtotal', function () {
    $product = Product::factory()->create(['name' => 'Produs Coș']);
    $variant = ProductVariant::factory()->for($product)->create(['price' => 7500, 'stock' => 5]);

    app(CartRepository::class)->add((string) $variant->id, 2);

    Livewire::test(CartPage::class)
        ->assertSee('Produs Coș')
        ->assertSee('150,00 lei'); // 75,00 * 2
});

it('shows an empty state when the cart has no lines', function () {
    Livewire::test(CartPage::class)
        ->assertSee('Coșul tău este gol')
        ->assertDontSee('Produs Coș');
});
