<?php

namespace Modules\Orders\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\OrderPlaced;
use Modules\Core\Events\PaymentCompleted;
use Modules\Orders\Listeners\CreateOrderFromCheckout;
use Modules\Orders\Listeners\MarkOrderPaid;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Orders is the single owner of every Order mutation. It listens to two
     * Core integration events: OrderPlaced (from Checkout, Part 8) to create the
     * order, and PaymentCompleted (from Payments, Part 11 — dormant for now) to
     * mark it paid.
     *
     * Registered explicitly rather than via auto-discovery, which only scans the
     * application's app/Listeners, not a module's.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            CreateOrderFromCheckout::class,
        ],
        PaymentCompleted::class => [
            MarkOrderPaid::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
