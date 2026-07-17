<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('lists active products as paginated JSON with the Money shape', function () {
    Product::factory()->create(['name' => 'Tricou Alb', 'price' => 12990, 'is_active' => true]);
    Product::factory()->create(['name' => 'Produs Ascuns', 'is_active' => false]);

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'name', 'slug', 'price' => ['minor', 'formatted', 'currency'], 'in_stock']],
            'links',
            'meta',
        ]);

    // Only the active product is returned.
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Tricou Alb')
        ->and($response->json('data.0.price'))->toBe([
            'minor' => 12990,
            'formatted' => '129,90 lei',
            'currency' => 'RON',
        ]);
});

it('filters the product list by a search term', function () {
    Product::factory()->create(['name' => 'Cana Emailată']);
    Product::factory()->create(['name' => 'Tricou Bumbac']);

    $response = $this->getJson('/api/v1/products?search=Cana');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Cana Emailată');
});

it('returns a product detail with its variants and attributes', function () {
    $product = Product::factory()->create(['name' => 'Tricou', 'price' => 9990]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => null, 'stock' => 5]);

    $attribute = Attribute::factory()->create(['name' => 'Culoare']);
    $value = AttributeValue::factory()->for($attribute)->create(['value' => 'Roșu', 'slug' => 'rosu']);
    $variant->attributeValues()->attach($value->id);

    $response = $this->getJson("/api/v1/products/{$product->slug}");

    $response->assertOk()
        ->assertJsonPath('data.name', 'Tricou')
        ->assertJsonPath('data.variants.0.price.minor', 9990)
        ->assertJsonPath('data.variants.0.price.currency', 'RON')
        ->assertJsonPath('data.variants.0.attributes.0.attribute', 'Culoare')
        ->assertJsonPath('data.variants.0.attributes.0.value', 'Roșu');
});

it('404s a detail request for an inactive product', function () {
    $product = Product::factory()->create(['is_active' => false]);

    $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
});

it('returns the active category tree', function () {
    $parent = Category::factory()->create(['name' => 'Îmbrăcăminte', 'is_active' => true]);
    Category::factory()->for($parent, 'parent')->create(['name' => 'Tricouri', 'is_active' => true]);
    Category::factory()->create(['name' => 'Ascunsă', 'is_active' => false]);

    $response = $this->getJson('/api/v1/categories');

    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'children']]]);

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.children'))->toHaveCount(1)
        ->and($response->json('data.0.children.0.name'))->toBe('Tricouri');
});
