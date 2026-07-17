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

it('registers the three Catalog resources on the admin panel', function () {
    $resources = filament()->getPanel('admin')->getResources();

    expect($resources)->toContain(
        \Modules\Catalog\Filament\Resources\ProductResource::class,
        \Modules\Catalog\Filament\Resources\CategoryResource::class,
        \Modules\Catalog\Filament\Resources\BrandResource::class,
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
]);

it('forbids a roleless user from the panel', function () {
    $plain = User::factory()->create();

    actingAs($plain);

    get('admin/products')->assertForbidden();
});
