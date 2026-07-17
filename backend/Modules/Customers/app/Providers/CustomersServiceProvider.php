<?php

namespace Modules\Customers\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CustomersServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Customers';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'customers';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Tell the framework's `auth` middleware where to send an unauthenticated
     * guest. Laravel 13's streamlined skeleton no longer defaults this to
     * `route('login')` — with no callback registered, a guest hitting a
     * `/cont/*` page gets a bare 401 instead of a redirect. Customers is the
     * module that owns the storefront login page, so it is the natural place
     * to wire this (the `login` route name itself is registered in
     * routes/web.php, deliberately NOT `storefront.login`, to match this
     * convention).
     */
    public function boot(): void
    {
        parent::boot();

        Authenticate::redirectUsing(fn () => route('login'));
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
