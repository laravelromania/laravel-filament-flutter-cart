<?php

declare(strict_types=1);

namespace Modules\Orders\Enums;

/**
 * The lifecycle of an order, as a backed enum stored in the `status` column.
 *
 * The value transitions are a small state machine: an order may only move along
 * the edges declared in {@see self::allowedTransitions()}. Every mutation of an
 * Order status — the admin "schimbă status" action, the {@see \Modules\Orders\Listeners\MarkOrderPaid}
 * listener, later Shipping/Payments events — asks {@see self::canTransitionTo()}
 * first, so an order can never jump from, say, `pending` straight to `shipped`.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Fulfilled = 'fulfilled';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /** Romanian label shown in the admin and the customer account. */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'În așteptare',
            self::Paid => 'Plătită',
            self::Fulfilled => 'În pregătire',
            self::Shipped => 'Expediată',
            self::Completed => 'Finalizată',
            self::Cancelled => 'Anulată',
            self::Refunded => 'Rambursată',
        };
    }

    /** A Filament badge colour name for the status column. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'info',
            self::Fulfilled => 'primary',
            self::Shipped => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Refunded => 'gray',
        };
    }

    /**
     * The states this one may transition into. Terminal states return `[]`.
     *
     * @return self[]
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Cancelled],
            self::Paid => [self::Fulfilled, self::Refunded],
            self::Fulfilled => [self::Shipped],
            self::Shipped => [self::Completed],
            self::Completed, self::Cancelled, self::Refunded => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * The allowed next states as a `value => label` map, for a Filament Select.
     *
     * @return array<string, string>
     */
    public function transitions(): array
    {
        $options = [];

        foreach ($this->allowedTransitions() as $status) {
            $options[$status->value] = $status->label();
        }

        return $options;
    }
}
