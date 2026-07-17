<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Catalog\Livewire\ProductIndex;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('lists only active products on the storefront index', function () {
    Product::factory()->create(['name' => 'Telefon Activ']);
    Product::factory()->create(['name' => 'Produs Ascuns', 'is_active' => false]);

    Livewire::test(ProductIndex::class)
        ->assertSee('Telefon Activ')
        ->assertDontSee('Produs Ascuns');
});

it('filters products by a search term against the name', function () {
    Product::factory()->create(['name' => 'Telefon Alpha']);
    Product::factory()->create(['name' => 'Bicicleta Beta']);

    Livewire::test(ProductIndex::class)
        ->set('search', 'Telefon')
        ->assertSee('Telefon Alpha')
        ->assertDontSee('Bicicleta Beta');
});

it('filters products by category slug', function () {
    $category = Category::factory()->create(['slug' => 'telefoane']);
    $inCategory = Product::factory()->create(['name' => 'Produs In Categorie']);
    $inCategory->categories()->attach($category);
    Product::factory()->create(['name' => 'Produs Fara Categorie']);

    Livewire::test(ProductIndex::class)
        ->set('category', 'telefoane')
        ->assertSee('Produs In Categorie')
        ->assertDontSee('Produs Fara Categorie');
});

it('filters products by brand slug', function () {
    $brand = Brand::factory()->create(['slug' => 'acme']);
    Product::factory()->for($brand)->create(['name' => 'Produs Acme']);
    Product::factory()->create(['name' => 'Produs Fara Brand']);

    Livewire::test(ProductIndex::class)
        ->set('brand', 'acme')
        ->assertSee('Produs Acme')
        ->assertDontSee('Produs Fara Brand');
});

it('filters products by an attribute value slug', function () {
    $attribute = Attribute::factory()->create(['slug' => 'culoare']);
    $red = AttributeValue::factory()->for($attribute, 'attribute')->create(['slug' => 'rosu']);

    $withRed = Product::factory()->create(['name' => 'Produs Rosu']);
    $variant = ProductVariant::factory()->for($withRed)->create();
    $variant->attributeValues()->attach($red);

    Product::factory()->create(['name' => 'Produs Neutru']);

    Livewire::test(ProductIndex::class)
        ->set('attributeFilters', ['rosu'])
        ->assertSee('Produs Rosu')
        ->assertDontSee('Produs Neutru');
});

it('filters products by a minimum price expressed in lei', function () {
    Product::factory()->create(['name' => 'Produs Ieftin', 'price' => 5000]);   // 50,00 lei
    Product::factory()->create(['name' => 'Produs Scump', 'price' => 50000]);   // 500,00 lei

    Livewire::test(ProductIndex::class)
        ->set('priceMin', '100')
        ->assertSee('Produs Scump')
        ->assertDontSee('Produs Ieftin');
});

it('orders products by price ascending and descending', function () {
    Product::factory()->create(['name' => 'Produs Scump', 'price' => 90000]);
    Product::factory()->create(['name' => 'Produs Ieftin', 'price' => 1000]);

    Livewire::test(ProductIndex::class)
        ->set('sort', 'pret-asc')
        ->assertSeeInOrder(['Produs Ieftin', 'Produs Scump'])
        ->set('sort', 'pret-desc')
        ->assertSeeInOrder(['Produs Scump', 'Produs Ieftin']);
});

it('paginates results and resets to page one when a filter changes', function () {
    Product::factory()->count(15)->create();

    Livewire::test(ProductIndex::class)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'termen-fara-rezultate')
        ->assertSet('paginators.page', 1);
});
