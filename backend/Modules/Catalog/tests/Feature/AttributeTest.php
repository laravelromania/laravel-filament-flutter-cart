<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has many attribute values', function () {
    $attribute = Attribute::factory()->create();
    AttributeValue::factory()->for($attribute, 'attribute')->count(3)->create();

    expect($attribute->values)->toHaveCount(3)
        ->and($attribute->values->first())->toBeInstanceOf(AttributeValue::class);
});

it('resolves the owning attribute from a value', function () {
    $attribute = Attribute::factory()->create();
    $value = AttributeValue::factory()->for($attribute, 'attribute')->create();

    expect($value->attribute)->toBeInstanceOf(Attribute::class)
        ->and($value->attribute->is($attribute))->toBeTrue();
});

it('deletes attribute values when the owning attribute is deleted (cascade)', function () {
    $attribute = Attribute::factory()->create();
    $value = AttributeValue::factory()->for($attribute, 'attribute')->create();

    $attribute->delete();

    expect(AttributeValue::find($value->id))->toBeNull();
});

it('enforces a unique slug per attribute', function () {
    $attribute = Attribute::factory()->create();
    AttributeValue::factory()->for($attribute, 'attribute')->create(['slug' => 'rosu']);

    expect(fn () => AttributeValue::factory()->for($attribute, 'attribute')->create(['slug' => 'rosu']))
        ->toThrow(QueryException::class);
});

it('allows the same value slug across two different attributes', function () {
    $color = Attribute::factory()->create();
    $size = Attribute::factory()->create();

    AttributeValue::factory()->for($color, 'attribute')->create(['slug' => 'm']);
    $value = AttributeValue::factory()->for($size, 'attribute')->create(['slug' => 'm']);

    expect($value->exists)->toBeTrue();
});
