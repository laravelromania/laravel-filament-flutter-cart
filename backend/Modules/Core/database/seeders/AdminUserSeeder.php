<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the dev/demo admin account (admin@shop.test / password). Runs before
 * RolesSeeder in {@see CoreDatabaseSeeder} so there is already a user row for
 * it to attach the `admin` role to. firstOrCreate on email keeps re-seeding
 * idempotent.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@shop.test'],
            [
                'name' => 'Admin Magazin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
