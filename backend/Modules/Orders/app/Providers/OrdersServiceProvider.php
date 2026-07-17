<?php

namespace Modules\Orders\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Livewire\Livewire;
use Modules\Core\Contracts\OrderLocator;
use Modules\Orders\Livewire\Account\OrderDetail;
use Modules\Orders\Livewire\Account\OrderHistory;
use Modules\Orders\Livewire\Storefront\OrderConfirmation;
use Modules\Orders\Services\EloquentOrderLocator;
use Nwidart\Modules\Support\ModuleServiceProvider;

class OrdersServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Orders';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'orders';

    public function register(): void
    {
        parent::register();

        // Core contract -> Orders implementation. Payments (Part 11) resolves the
        // contract to load a Payable by its reference, never importing Order.
        $this->app->bind(OrderLocator::class, EloquentOrderLocator::class);
    }

    /**
     * Register the account order components by name so the Customers module can
     * embed the history with `@livewire('orders.account-orders')` — a string
     * reference only, keeping Customers free of any Orders class import.
     */
    public function boot(): void
    {
        parent::boot();

        Livewire::component('orders.account-orders', OrderHistory::class);
        Livewire::component('orders.account-order', OrderDetail::class);
        Livewire::component('orders.order-confirmation', OrderConfirmation::class);
    }

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
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
