<?php

declare(strict_types=1);

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Setting;

/**
 * Seeds the default store settings. Only what the storefront needs to boot.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'shop.currency'],
            ['value' => 'RON'],
        );
    }
}
