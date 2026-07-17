<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Attribute;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 1_000_000),
        ];
    }
}
