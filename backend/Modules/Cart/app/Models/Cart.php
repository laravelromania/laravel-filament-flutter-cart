<?php

declare(strict_types=1);

namespace Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The persisted basket of a single user (one row per user — `user_id` is
 * unique). Only {@see DatabaseCart} touches this; the rest of the app depends on
 * the CartRepository contract, never on this model.
 */
class Cart extends Model
{
    protected $fillable = [
        'user_id',
    ];

    /**
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
