<?php

declare(strict_types=1);

namespace Modules\Customers\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Customers\Database\Factories\CustomerFactory;

/**
 * The storefront profile attached to a `users` row. Deliberately thin: the
 * account itself (name, email, password) stays on App\Models\User via the
 * shared `web` guard (see the Part 7 article for the single-guard decision);
 * this model only carries storefront-specific data (phone) plus the address
 * book. `Orders` (Part 9) will belong to a Customer, not directly to a User.
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Only `name` exists on the users table (no first/last split), so the
     * "full name" is simply the account holder's name.
     */
    public function fullName(): string
    {
        return $this->user?->name ?? '';
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
