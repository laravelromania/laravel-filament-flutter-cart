<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the two store roles and gives the dev admin the `admin` role.
 *
 * Permissions themselves are added per-module as features land; Core only
 * guarantees the roles exist so panels and policies have something to check.
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure freshly created roles are visible to the rest of this run.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::where('email', 'admin@shop.test')->first();

        if ($admin && ! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
