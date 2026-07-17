<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Setting;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('renders the storefront home on the layout', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Fundația este pe picioare')
        ->assertSee('129,90 lei')
        ->assertSee('lang="ro"', false);
});

it('lets an admin open the shop settings page but keeps out roleless users', function () {
    Setting::query()->create(['key' => 'shop.currency', 'value' => 'RON']);
    Role::findOrCreate('admin', 'web');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/manage-shop-settings')
        ->assertOk()
        ->assertSee('Setări magazin');

    // A user without a staff role is denied.
    $this->actingAs(User::factory()->create())
        ->get('/admin/manage-shop-settings')
        ->assertForbidden();
});
