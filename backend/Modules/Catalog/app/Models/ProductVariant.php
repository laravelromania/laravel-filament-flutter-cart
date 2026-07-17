<?php

declare(strict_types=1);

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Catalog\Database\Factories\ProductVariantFactory;
use Modules\Core\Casts\MoneyCast;
use Modules\Core\ValueObjects\Money;

/**
 * A concrete purchasable version of a {@see Product} (one SKU, one stock
 * count, one set of {@see AttributeValue} picks such as Roșu/M). `price` is a
 * nullable override in minor units (bani) via MoneyCast: null means "use the
 * parent product's price" (see {@see self::effectivePrice()}).
 */
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsToMany<AttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'attribute_value_variant',
            'variant_id',
            'attribute_value_id',
        );
    }

    /**
     * The price actually charged for this variant: its own override when set,
     * otherwise the parent product's price.
     */
    public function effectivePrice(): Money
    {
        return $this->price ?? $this->product->price;
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'stock' => 'integer',
        ];
    }

    protected static function newFactory(): ProductVariantFactory
    {
        return ProductVariantFactory::new();
    }
}
