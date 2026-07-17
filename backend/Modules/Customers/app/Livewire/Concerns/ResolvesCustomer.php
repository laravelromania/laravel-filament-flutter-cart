<?php

declare(strict_types=1);

namespace Modules\Customers\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Modules\Customers\Models\Customer;

/**
 * Every Account\* component needs "the Customer profile row for whoever is
 * signed in". `/cont` only requires `auth` (not a role) — so a staff account
 * that happens to visit it should get a profile lazily created rather than a
 * crash. Customers created through Register always have one already;
 * `firstOrCreate` makes the lookup safe either way.
 */
trait ResolvesCustomer
{
    protected function customer(): Customer
    {
        return Customer::firstOrCreate(['user_id' => Auth::id()]);
    }
}
