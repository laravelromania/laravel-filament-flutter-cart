<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Database\Factories\AttributeFactory;

/**
 * A product characteristic that can be sliced into discrete values
 * (e.g. "Culoare" -> Roșu/Verde, "Mărime" -> S/M/L). Values are attached to
 * {@see ProductVariant} records through the `attribute_value_variant` pivot.
 */
class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return HasMany<AttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    protected static function newFactory(): AttributeFactory
    {
        return AttributeFactory::new();
    }
}
