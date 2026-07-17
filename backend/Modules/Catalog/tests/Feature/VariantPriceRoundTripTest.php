<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Catalog\Filament\Resources\ProductResource\Pages\EditProduct;
use Modules\Catalog\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductVariant;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('stores a new variant price typed in lei as bani through the relation manager', function () {
    $product = Product::factory()->create(['price' => 10000]);

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('create', data: [
            'sku' => 'RM-NEW-SKU',
            'price' => '49.90',
            'stock' => 7,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_variants', [
        'product_id' => $product->id,
        'sku' => 'RM-NEW-SKU',
        'price' => 4990,
    ]);
});

it('round-trips an existing variant price override in lei as bani on edit', function () {
    $product = Product::factory()->create(['price' => 10000]);
    $variant = ProductVariant::factory()->for($product)->create([
        'sku' => 'RM-EDIT-SKU',
        'price' => 5000,
    ]);

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('edit', record: $variant, data: [
            'price' => '199.50',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_variants', [
        'id' => $variant->id,
        'price' => 19950,
    ]);
});

it('clears the variant price override to null when left empty', function () {
    $product = Product::factory()->create(['price' => 10000]);
    $variant = ProductVariant::factory()->for($product)->create([
        'sku' => 'RM-NULL-SKU',
        'price' => 5000,
    ]);

    Livewire::test(VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => EditProduct::class,
    ])
        ->callTableAction('edit', record: $variant, data: [
            'price' => null,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('product_variants', [
        'id' => $variant->id,
        'price' => null,
    ]);
});
