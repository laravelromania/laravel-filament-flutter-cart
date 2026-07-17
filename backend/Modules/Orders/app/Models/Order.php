<?php

declare(strict_types=1);

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Casts\MoneyCast;
use Modules\Core\Contracts\Payable;
use Modules\Core\Contracts\Shippable;
use Modules\Core\DataObjects\ShippingContext;
use Modules\Core\ValueObjects\Money;
use Modules\Orders\Database\Factories\OrderFactory;
use Modules\Orders\Enums\OrderStatus;

/**
 * A placed order. Built by {@see \Modules\Orders\Listeners\CreateOrderFromCheckout}
 * from the Core OrderDraft, it is a self-contained snapshot: the address columns,
 * the money totals and the line items are frozen at checkout time.
 *
 * It implements the two Core role interfaces so a Payment gateway (Part 11) can
 * charge it and a Shipping provider (Part 10) can create a shipment for it —
 * without either module (or Core) ever depending on Orders.
 */
class Order extends Model implements Payable, Shippable
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'status',
        'user_id',
        'email',
        'customer_name',
        'phone',
        'billing',
        'shipping',
        'items_subtotal',
        'shipping_code',
        'shipping_label',
        'shipping_total',
        'payment_code',
        'total',
        'awb',
        'paid_at',
    ];

    /**
     * Stamp the human order number from the auto-increment id right after the
     * row exists (CMD-000123). Done here rather than in the listener so orders
     * created any other way (factory, seeder) get a number too. saveQuietly
     * avoids a second round of model events.
     */
    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            if ($order->number === null) {
                $order->forceFill([
                    'number' => 'CMD-'.str_pad((string) $order->getKey(), 6, '0', STR_PAD_LEFT),
                ])->saveQuietly();
            }
        });
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // --- Core\Contracts\Payable -------------------------------------------

    public function payableReference(): string
    {
        return (string) $this->number;
    }

    public function payableAmount(): Money
    {
        return $this->total;
    }

    // --- Core\Contracts\Shippable -----------------------------------------

    public function shippableReference(): string
    {
        return (string) $this->number;
    }

    public function shippingContext(): ShippingContext
    {
        $shipping = $this->shipping ?? [];
        $units = (int) $this->items()->sum('quantity');

        return new ShippingContext(
            county: (string) ($shipping['county'] ?? ''),
            city: (string) ($shipping['city'] ?? ''),
            postalCode: (string) ($shipping['postalCode'] ?? ''),
            weightKg: max(0.5, $units * 0.5),
            cartSubtotal: $this->items_subtotal,
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'billing' => 'array',
            'shipping' => 'array',
            'items_subtotal' => MoneyCast::class,
            'shipping_total' => MoneyCast::class,
            'total' => MoneyCast::class,
            'paid_at' => 'datetime',
        ];
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
