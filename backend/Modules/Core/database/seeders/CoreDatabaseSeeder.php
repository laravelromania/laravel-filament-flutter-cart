<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            // Must run first: RolesSeeder attaches the `admin` role to this user.
            AdminUserSeeder::class,
            RolesSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
