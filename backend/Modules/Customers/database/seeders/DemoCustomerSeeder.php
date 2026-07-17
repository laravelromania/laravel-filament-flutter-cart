<?php

declare(strict_types=1);

namespace Modules\Customers\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Customers\Models\Address;
use Modules\Customers\Models\Customer;

/**
 * Seeds one demo shopper (customer@shop.test / password) with a Customer
 * profile and a default shipping address, so /cont has something to show and
 * the checkout wizard's "pick an address from the book" step is exercisable
 * without registering a fresh account by hand. Idempotent — firstOrCreate on
 * email/user_id/type, so re-seeding won't duplicate rows.
 */
class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'customer@shop.test'],
            [
                'name' => 'Ana Ionescu',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $customer = Customer::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['phone' => '0722111333'],
        );

        Address::query()->firstOrCreate(
            ['customer_id' => $customer->id, 'type' => 'shipping'],
            [
                'name' => $user->name,
                'phone' => '0722111333',
                'county' => 'Cluj',
                'city' => 'Cluj-Napoca',
                'street' => 'Str. Memorandumului 28',
                'postal_code' => '400114',
                'is_default' => true,
            ],
        );
    }
}
