<?php

use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read a value from the settings store.
     *
     * setting('shop.currency', 'RON') returns the stored value or the default.
     * Called with no key it returns the whole map. Kept intentionally tiny —
     * this is a key/value table, not a typed-settings framework.
     *
     * @return mixed
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        // Guard the very early boot / pre-migration state so the storefront and
        // the admin panel still render before the settings table exists.
        if (! Schema::hasTable('settings')) {
            return $key === null ? [] : $default;
        }

        $values = Setting::query()->pluck('value', 'key');

        if ($key === null) {
            return $values->all();
        }

        return $values->get($key, $default);
    }
}
