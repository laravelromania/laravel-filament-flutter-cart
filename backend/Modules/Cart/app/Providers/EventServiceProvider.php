<?php

namespace Modules\Cart\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Cart\Listeners\MergeGuestCart;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * When a visitor authenticates, fold their guest (session) cart into their
     * persistent (database) cart. Registered explicitly rather than via
     * auto-discovery, which only scans the application's app/Listeners — not a
     * module's.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        Login::class => [
            MergeGuestCart::class,
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
