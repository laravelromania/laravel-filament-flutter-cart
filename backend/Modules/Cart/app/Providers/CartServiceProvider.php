<?php

namespace Modules\Cart\Providers;

use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Modules\Cart\Livewire\CartDrawer;
use Modules\Cart\Livewire\CartPage;
use Modules\Cart\Livewire\MiniCart;
use Modules\Cart\Services\DatabaseCart;
use Modules\Cart\Services\SessionCart;
use Modules\Core\Contracts\CartRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CartServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Cart';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'cart';

    /**
     * Bind the CartRepository contract to a concrete cart chosen by auth state:
     * a database-backed basket for logged-in customers, a session basket for
     * guests. It is a plain `bind` (NOT a singleton) so the choice is re-made on
     * every resolution — a request that logs a user in mid-flight must not keep
     * handing out the guest cart.
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(CartRepository::class, function ($app) {
            return Auth::check()
                ? $app->make(DatabaseCart::class)
                : $app->make(SessionCart::class);
        });
    }

    /**
     * Register the Livewire components by name so the Core storefront layout can
     * embed the mini-cart and the drawer (`@livewire('cart.mini-cart')`) without
     * Core ever referencing a concrete Cart class.
     */
    public function boot(): void
    {
        parent::boot();

        Livewire::component('cart.mini-cart', MiniCart::class);
        Livewire::component('cart.drawer', CartDrawer::class);
        Livewire::component('cart.page', CartPage::class);
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
