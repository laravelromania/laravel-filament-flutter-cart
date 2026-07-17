<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('registers the Catalog resources on the admin panel', function () {
    $resources = filament()->getPanel('admin')->getResources();

    expect($resources)->toContain(
        \Modules\Catalog\Filament\Resources\ProductResource::class,
        \Modules\Catalog\Filament\Resources\CategoryResource::class,
        \Modules\Catalog\Filament\Resources\BrandResource::class,
        \Modules\Catalog\Filament\Resources\AttributeResource::class,
    );
});

it('lets an admin open the Catalog resource pages', function (string $path) {
    actingAs($this->admin);

    get($path)->assertOk();
})->with([
    'admin/products',
    'admin/products/create',
    'admin/categories',
    'admin/categories/create',
    'admin/brands',
    'admin/brands/create',
    'admin/attributes',
    'admin/attributes/create',
]);

it('shows the variants relation manager on the product edit page', function () {
    actingAs($this->admin);

    $product = \Modules\Catalog\Models\Product::factory()->create();
    $variant = \Modules\Catalog\Models\ProductVariant::factory()->for($product)->create(['sku' => 'EDIT-PAGE-SKU']);

    expect(\Modules\Catalog\Filament\Resources\ProductResource::getRelations())
        ->toContain(\Modules\Catalog\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager::class);

    // The Edit page itself renders fine with the manager wired in...
    get("admin/products/{$product->getRouteKey()}/edit")->assertOk();

    // ...and the relation manager component (mounted the way Filament embeds
    // it into the page) actually lists this product's variants.
    \Livewire\Livewire::test(
        \Modules\Catalog\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager::class,
        ['ownerRecord' => $product, 'pageClass' => \Modules\Catalog\Filament\Resources\ProductResource\Pages\EditProduct::class],
    )->assertSee('EDIT-PAGE-SKU');
});

it('shows the values relation manager on the attribute edit page', function () {
    actingAs($this->admin);

    $attribute = \Modules\Catalog\Models\Attribute::factory()->create();
    $value = \Modules\Catalog\Models\AttributeValue::factory()->for($attribute, 'attribute')->create(['value' => 'Verde Smarald']);

    get("admin/attributes/{$attribute->getRouteKey()}/edit")->assertOk();

    \Livewire\Livewire::test(
        \Modules\Catalog\Filament\Resources\AttributeResource\RelationManagers\ValuesRelationManager::class,
        ['ownerRecord' => $attribute, 'pageClass' => \Modules\Catalog\Filament\Resources\AttributeResource\Pages\EditAttribute::class],
    )->assertSee('Verde Smarald');
});

it('forbids a roleless user from the panel', function () {
    $plain = User::factory()->create();

    actingAs($plain);

    get('admin/products')->assertForbidden();
});
