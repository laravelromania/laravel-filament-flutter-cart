<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Category;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('resolves its parent category', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    expect($child->parent)->toBeInstanceOf(Category::class)
        ->and($child->parent->is($parent))->toBeTrue();
});

it('resolves its children ordered by position', function () {
    $parent = Category::factory()->create();
    $second = Category::factory()->create(['parent_id' => $parent->id, 'position' => 2]);
    $first = Category::factory()->create(['parent_id' => $parent->id, 'position' => 1]);

    expect($parent->children)->toHaveCount(2)
        ->and($parent->children->pluck('id')->all())->toBe([$first->id, $second->id]);
});

it('leaves a root category without a parent', function () {
    $root = Category::factory()->create();

    expect($root->parent_id)->toBeNull()
        ->and($root->parent)->toBeNull();
});

it('nulls the parent_id of children when a parent is deleted', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    $parent->delete();

    expect($child->fresh()->parent_id)->toBeNull();
});
