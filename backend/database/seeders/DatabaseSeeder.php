<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;
use Modules\Customers\Database\Seeders\CustomersDatabaseSeeder;

/**
 * `php artisan migrate:fresh --seed` entry point: runs the module seeders in
 * dependency order so the result is a browsable demo shop, not an empty DB.
 *
 *  1. Core   — roles (admin/manager), shop settings, and the dev admin user
 *              (admin@shop.test). Must run first: everything else either
 *              needs `users` to exist or is harmless without it.
 *  2. Catalog — brands, a category tree, and ~a dozen products with priced,
 *              stocked variants, so `/produse` has something to list.
 *  3. Customers — one demo shopper (customer@shop.test) with an address, so
 *              `/cont` and the checkout "pick from address book" step have
 *              something to show.
 *
 * Every module seeder in this chain is idempotent (firstOrCreate keyed on
 * slug/SKU/email), so re-running this — in dev or CI — never duplicates rows.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CoreDatabaseSeeder::class,
            CatalogDatabaseSeeder::class,
            CustomersDatabaseSeeder::class,
        ]);
    }
}
