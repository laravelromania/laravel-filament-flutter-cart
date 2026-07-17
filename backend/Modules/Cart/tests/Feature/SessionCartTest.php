<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cart\Services\SessionCart;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('adds a variant and exposes it as a line with the correct Money subtotal', function () {
    $product = Product::factory()->create(['name' => 'Tricou', 'price' => 10000]);
    $variant = ProductVariant::factory()->for($product)->create(['price' => 12990, 'stock' => 5]);

    $cart = app(SessionCart::class);
    $cart->add((string) $variant->id, 2);

    $data = $cart->get();

    expect($data->itemCount)->toBe(2)
        ->and($data->lines)->toHaveCount(1)
        ->and($data->lines[0]->variantId)->toBe((string) $variant->id)
        ->and($data->lines[0]->quantity)->toBe(2)
        ->and($data->lines[0]->unitPrice->getMinorAmount())->toBe(12990)
        ->and($data->lines[0]->lineTotal->getMinorAmount())->toBe(25980)
        ->and($data->subtotal->getMinorAmount())->toBe(25980)
        ->and($data->subtotal->format())->toBe('259,80 lei');
});

it('accumulates quantity when the same variant is added twice', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 5000, 'stock' => 10]);

    $cart = app(SessionCart::class);
    $cart->add((string) $variant->id, 1);
    $cart->add((string) $variant->id, 2);

    expect($cart->get()->itemCount)->toBe(3)
        ->and($cart->get()->lines)->toHaveCount(1);
});

it('updates a line quantity and removes a line', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 5000, 'stock' => 10]);

    $cart = app(SessionCart::class);
    $cart->add((string) $variant->id, 1);

    $cart->update((string) $variant->id, 4);
    expect($cart->get()->itemCount)->toBe(4);

    $cart->remove((string) $variant->id);
    expect($cart->get()->isEmpty())->toBeTrue()
        ->and($cart->get()->subtotal->isZero())->toBeTrue();
});

it('builds the line name from the product name and variant attributes', function () {
    $product = Product::factory()->create(['name' => 'Tricou']);
    $size = Attribute::factory()->create(['name' => 'Mărime']);
    $medium = AttributeValue::factory()->for($size)->create(['value' => 'M']);
    $variant = ProductVariant::factory()->for($product)->create(['price' => 5000, 'stock' => 5]);
    $variant->attributeValues()->attach($medium);

    $cart = app(SessionCart::class);
    $cart->add((string) $variant->id, 1);

    expect($cart->get()->lines[0]->name)->toContain('Tricou')
        ->and($cart->get()->lines[0]->name)->toContain('M');
});

it('clears the whole cart', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->for($product)->create(['price' => 5000, 'stock' => 5]);

    $cart = app(SessionCart::class);
    $cart->add((string) $variant->id, 3);
    $cart->clear();

    expect($cart->get()->isEmpty())->toBeTrue();
});
