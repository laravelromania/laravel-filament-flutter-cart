<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Core\ValueObjects\Money;
use Spatie\MediaLibrary\HasMedia;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('belongs to a brand', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->for($brand)->create();

    expect($product->brand)->toBeInstanceOf(Brand::class)
        ->and($product->brand->is($brand))->toBeTrue()
        ->and($brand->products)->toHaveCount(1);
});

it('has a nullable brand (SET NULL on delete)', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->for($brand)->create();

    $brand->delete();

    expect($product->fresh()->brand_id)->toBeNull();
});

it('belongs to many categories through the pivot', function () {
    $product = Product::factory()->create();
    $a = Category::factory()->create();
    $b = Category::factory()->create();

    $product->categories()->attach([$a->id, $b->id]);

    expect($product->categories)->toHaveCount(2)
        ->and($product->categories->pluck('id'))->toContain($a->id, $b->id)
        // reverse side of the pivot resolves too
        ->and($a->fresh()->products->pluck('id'))->toContain($product->id);
});

it('returns the price as a Money value object via the cast', function () {
    $product = Product::factory()->create(['price' => 12990]);

    expect($product->fresh()->price)->toBeInstanceOf(Money::class)
        ->and($product->fresh()->price->getMinorAmount())->toBe(12990)
        ->and($product->fresh()->price->format())->toBe('129,90 lei');
});

it('accepts a Money instance when writing the price', function () {
    $product = Product::factory()->create(['price' => Money::of(4990)]);

    expect($product->fresh()->price->getMinorAmount())->toBe(4990);
});

it('implements HasMedia and registers an images collection', function () {
    $product = Product::factory()->create();

    expect($product)->toBeInstanceOf(HasMedia::class)
        ->and($product->getRegisteredMediaCollections()->pluck('name'))
        ->toContain('images');
});
