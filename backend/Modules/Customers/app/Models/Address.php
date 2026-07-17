<?php

declare(strict_types=1);

namespace Modules\Customers\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Customers\Database\Factories\AddressFactory;

/**
 * A billing or shipping address in a customer's address book. `type` is kept
 * as a plain validated string (allowed values enforced in the migration's
 * `enum` column and in the Livewire form) rather than a PHP backed enum —
 * there is no behaviour attached to it yet, just a label.
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'name',
        'phone',
        'county',
        'city',
        'street',
        'postal_code',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    protected static function newFactory(): AddressFactory
    {
        return AddressFactory::new();
    }
}
