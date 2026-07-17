<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeValue;

/**
 * @extends Factory<AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = ucfirst(fake()->unique()->word());

        return [
            'attribute_id' => Attribute::factory(),
            'value' => $value,
            'slug' => Str::slug($value).'-'.fake()->unique()->numberBetween(1, 1_000_000),
        ];
    }
}
