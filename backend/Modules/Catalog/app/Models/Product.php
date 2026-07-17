<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Database\Factories\ProductFactory;
use Modules\Core\Casts\MoneyCast;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A catalog product. The `price` column stores minor units (bani) and is
 * exposed as a {@see \Modules\Core\ValueObjects\Money} value object through
 * MoneyCast. Images live in the `images` media collection (spatie/medialibrary).
 */
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'brand_id',
        'price',
        'is_active',
    ];

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * The variant shown/added-to-cart by default: the first one created
     * (or null when the product has no variants at all).
     */
    public function defaultVariant(): ?ProductVariant
    {
        return $this->variants()->orderBy('id')->first();
    }

    /**
     * Whether the product can currently be sold. Products without variants
     * carry no inventory count, so they're always considered in stock;
     * products with variants are in stock when at least one variant has
     * stock left.
     */
    public function inStock(): bool
    {
        if (! $this->variants()->exists()) {
            return true;
        }

        return $this->variants()->where('stock', '>', 0)->exists();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
