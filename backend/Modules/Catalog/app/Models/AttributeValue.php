<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Catalog\Database\Factories\AttributeValueFactory;

/**
 * A single value of an {@see Attribute} (e.g. "Roșu" for "Culoare"). The
 * `slug` is unique per attribute (not globally) so "m" can mean "Mărime: M"
 * and, on another attribute, something else entirely.
 */
class AttributeValue extends Model
{
    /** @use HasFactory<AttributeValueFactory> */
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
    ];

    /**
     * @return BelongsTo<Attribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * @return BelongsToMany<ProductVariant, $this>
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'attribute_value_variant',
            'attribute_value_id',
            'variant_id',
        );
    }

    protected static function newFactory(): AttributeValueFactory
    {
        return AttributeValueFactory::new();
    }
}
