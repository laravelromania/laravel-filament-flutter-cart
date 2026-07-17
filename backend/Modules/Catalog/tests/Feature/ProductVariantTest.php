<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Modules\Core\ValueObjects\Money;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has many product variants', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->for($product)->count(2)->create();

    expect($product->variants)->toHaveCount(2)
        ->and($product->variants->first())->toBeInstanceOf(ProductVariant::class);
});

it('resolves the default variant as the first one', function () {
    $product = Product::factory()->create();
    $first = ProductVariant::factory()->for($product)->create();
    ProductVariant::factory()->for($product)->create();

    expect($product->defaultVariant())->not->toBeNull()
        ->and($product->defaultVariant()->is($first))->toBeTrue();
});

it('returns null for defaultVariant when the product has no variants', function () {
    $product = Product::factory()->create();

    expect($product->defaultVariant())->toBeNull();
});

it('falls back to the product price when the variant price is null', function () {
    $product = Product::factory()->create(['price' => 12990]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => null]);

    expect($variant->price)->toBeNull()
        ->and($variant->effectivePrice())->toBeInstanceOf(Money::class)
        ->and($variant->effectivePrice()->getMinorAmount())->toBe(12990);
});

it('uses the variant price override when present', function () {
    $product = Product::factory()->create(['price' => 12990]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => 4990]);

    expect($variant->effectivePrice()->getMinorAmount())->toBe(4990);
});

it('reports inStock based on its own stock quantity', function () {
    $inStock = ProductVariant::factory()->create(['stock' => 5]);
    $outOfStock = ProductVariant::factory()->create(['stock' => 0]);

    expect($inStock->inStock())->toBeTrue()
        ->and($outOfStock->inStock())->toBeFalse();
});

it('treats a product with no variants as in stock (no inventory tracked)', function () {
    $product = Product::factory()->create();

    expect($product->variants)->toHaveCount(0)
        ->and($product->inStock())->toBeTrue();
});

it('reports a product as in stock when any of its variants has stock', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->for($product)->create(['stock' => 0]);
    ProductVariant::factory()->for($product)->create(['stock' => 3]);

    expect($product->inStock())->toBeTrue();
});

it('reports a product as out of stock when all of its variants are out of stock', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->for($product)->create(['stock' => 0]);
    ProductVariant::factory()->for($product)->create(['stock' => 0]);

    expect($product->inStock())->toBeFalse();
});

it('attaches attribute values to a variant through the attribute_value_variant pivot', function () {
    $color = Attribute::factory()->create(['name' => 'Culoare', 'slug' => 'culoare']);
    $red = AttributeValue::factory()->for($color, 'attribute')->create(['value' => 'Roșu', 'slug' => 'rosu']);
    $size = Attribute::factory()->create(['name' => 'Mărime', 'slug' => 'marime']);
    $medium = AttributeValue::factory()->for($size, 'attribute')->create(['value' => 'M', 'slug' => 'm']);

    $variant = ProductVariant::factory()->create();
    $variant->attributeValues()->attach([$red->id, $medium->id]);

    expect($variant->attributeValues)->toHaveCount(2)
        ->and($variant->attributeValues->pluck('id'))->toContain($red->id, $medium->id)
        // reverse side of the pivot resolves too
        ->and($red->fresh()->variants->pluck('id'))->toContain($variant->id);
});
