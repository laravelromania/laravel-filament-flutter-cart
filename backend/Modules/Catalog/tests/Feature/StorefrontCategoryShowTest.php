<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Catalog\Livewire\CategoryShow;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('lists only the products attached to the bound category', function () {
    $category = Category::factory()->create(['name' => 'Laptopuri', 'slug' => 'laptopuri']);
    $inCategory = Product::factory()->create(['name' => 'Laptop Pro']);
    $inCategory->categories()->attach($category);
    Product::factory()->create(['name' => 'Produs Din Alta Parte']);

    Livewire::test(CategoryShow::class, ['category' => $category])
        ->assertSee('Laptopuri')
        ->assertSee('Laptop Pro')
        ->assertDontSee('Produs Din Alta Parte');
});

it('still honours the search filter inside a category', function () {
    $category = Category::factory()->create(['slug' => 'accesorii']);
    $husa = Product::factory()->create(['name' => 'Husa Telefon']);
    $incarcator = Product::factory()->create(['name' => 'Incarcator Rapid']);
    $category->products()->attach([$husa->id, $incarcator->id]);

    Livewire::test(CategoryShow::class, ['category' => $category])
        ->set('search', 'Husa')
        ->assertSee('Husa Telefon')
        ->assertDontSee('Incarcator Rapid');
});
