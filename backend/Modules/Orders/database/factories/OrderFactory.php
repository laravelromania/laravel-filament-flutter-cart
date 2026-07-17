<?php

declare(strict_types=1);

namespace Modules\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $address = [
            'name' => fake()->name(),
            'phone' => fake()->numerify('07########'),
            'county' => fake()->randomElement(['Cluj', 'Iași', 'Timiș', 'București']),
            'city' => fake()->city(),
            'street' => fake()->streetAddress(),
            'postalCode' => fake()->numerify('######'),
        ];

        $itemsSubtotal = fake()->numberBetween(5000, 50000);
        $shipping = 1999;

        return [
            // `number` is stamped by the model's created hook.
            'status' => OrderStatus::Pending,
            'user_id' => null,
            'email' => fake()->safeEmail(),
            'customer_name' => $address['name'],
            'phone' => $address['phone'],
            'billing' => $address,
            'shipping' => $address,
            'items_subtotal' => $itemsSubtotal,
            'shipping_code' => 'flat',
            'shipping_label' => 'Livrare standard prin curier',
            'shipping_total' => $shipping,
            'payment_code' => 'mock',
            'total' => $itemsSubtotal + $shipping,
            'awb' => null,
            'paid_at' => null,
        ];
    }
}
