<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Catalog\Filament\Resources\ProductResource\Pages\CreateProduct;
use Modules\Catalog\Filament\Resources\ProductResource\Pages\EditProduct;
use Modules\Catalog\Models\Product;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('stores the price typed in lei as bani (minor units) on create', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Produs test',
            'slug' => 'produs-test',
            'price' => '129.90',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'slug' => 'produs-test',
        'price' => 12990,
    ]);
});

it('round-trips the price in lei as bani (minor units) on edit', function () {
    $product = Product::factory()->create(['price' => 5000]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'price' => '199.50',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'price' => 19950,
    ]);
});
