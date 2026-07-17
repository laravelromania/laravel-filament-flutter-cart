<?php

namespace Modules\Checkout\Providers;

use Livewire\Livewire;
use Modules\Checkout\Drivers\MockPaymentGateway;
use Modules\Checkout\Drivers\MockShippingProvider;
use Modules\Checkout\Livewire\Checkout;
use Modules\Checkout\Services\PaymentManager;
use Modules\Checkout\Services\ShippingManager;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CheckoutServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Checkout';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'checkout';

    /**
     * Register the driver registries as singletons, each pre-loaded with the
     * Part-8 mock driver. They are singletons on purpose: the Shipping (Part 10)
     * and Payments (Part 11) modules resolve these same instances in their own
     * boot() and `register()` their real drivers into them, so a later module
     * extends the checkout without Checkout depending on it.
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(ShippingManager::class, function ($app): ShippingManager {
            $manager = new ShippingManager();
            $manager->register($app->make(MockShippingProvider::class));

            return $manager;
        });

        $this->app->singleton(PaymentManager::class, function ($app): PaymentManager {
            $manager = new PaymentManager();
            $manager->register($app->make(MockPaymentGateway::class));

            return $manager;
        });
    }

    /**
     * Register the Livewire wizard by name so it can be routed to and embedded.
     */
    public function boot(): void
    {
        parent::boot();

        Livewire::component('checkout.checkout', Checkout::class);
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
