<?php

declare(strict_types=1);

namespace Modules\Customers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Customers\Models\Address;
use Modules\Customers\Models\Customer;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'type' => fake()->randomElement(['billing', 'shipping']),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'county' => fake()->randomElement(['Cluj', 'Iași', 'Timiș', 'București']),
            'city' => fake()->city(),
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'is_default' => false,
        ];
    }
}
