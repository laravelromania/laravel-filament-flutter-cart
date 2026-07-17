<?php

namespace Modules\Shipping\Providers;

use Modules\Checkout\Services\ShippingManager;
use Modules\Core\Contracts\ShipmentService;
use Modules\Shipping\Drivers\CargusProvider;
use Modules\Shipping\Drivers\FlatRateProvider;
use Modules\Shipping\Drivers\SamedayProvider;
use Modules\Shipping\Drivers\WeightBasedProvider;
use Modules\Shipping\Drivers\ZoneProvider;
use Modules\Shipping\Services\CourierShipmentService;
use Nwidart\Modules\Support\ModuleServiceProvider;

/**
 * Wires the Shipping module into the store WITHOUT the store knowing it exists.
 *
 * `register()` builds each driver from `config/config.php` (fed by `.env`) and
 * binds the Core {@see ShipmentService} contract to this module's implementation
 * — so Orders can generate an AWB by resolving a Core contract only.
 *
 * `boot()` resolves Checkout's {@see ShippingManager} singleton (the pre-loaded
 * registry from Part 8) and `register()`s the real drivers into it. Because boot
 * runs after every module's register(), the singleton already exists; we extend
 * it, never re-bind it. The `flat` driver reuses the code Part 8's mock claimed,
 * quietly replacing the placeholder; the rest are new methods that now appear at
 * checkout on their own.
 */
class ShippingServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Shipping';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'shipping';

    /**
     * The driver classes registered into the ShippingManager, in display order.
     *
     * @var array<int, class-string<\Modules\Core\Contracts\ShippingProvider>>
     */
    private const DRIVERS = [
        FlatRateProvider::class,
        WeightBasedProvider::class,
        ZoneProvider::class,
        SamedayProvider::class,
        CargusProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->registerDrivers();

        // Core contract → Shipping implementation. Guarded by app()->bound() on
        // the Orders side, so the AWB action simply hides when Shipping is off.
        $this->app->bind(ShipmentService::class, CourierShipmentService::class);
    }

    public function boot(): void
    {
        parent::boot();

        // Extend the SAME singleton Checkout created in Part 8 — never re-bind it.
        $manager = $this->app->make(ShippingManager::class);

        foreach (self::DRIVERS as $driver) {
            $manager->register($this->app->make($driver));
        }
    }

    /**
     * Bind each driver as a singleton built from config. Config is merged in
     * parent::boot() (registerConfig), but these closures resolve lazily — the
     * first hit is from boot() above, by which point the config is available.
     */
    private function registerDrivers(): void
    {
        $this->app->singleton(FlatRateProvider::class, fn (): FlatRateProvider => new FlatRateProvider(
            (int) config('shipping.flat.amount', 1999),
            (string) config('shipping.flat.label', 'Livrare standard (tarif fix)'),
        ));

        $this->app->singleton(WeightBasedProvider::class, fn (): WeightBasedProvider => new WeightBasedProvider(
            (array) config('shipping.weight.tiers', []),
            (int) config('shipping.weight.fallback', 6000),
            (string) config('shipping.weight.label', 'Livrare în funcție de greutate'),
        ));

        $this->app->singleton(ZoneProvider::class, fn (): ZoneProvider => new ZoneProvider(
            (array) config('shipping.zone.counties', []),
            (array) config('shipping.zone.rates', []),
            (string) config('shipping.zone.default_zone', 'national'),
            (string) config('shipping.zone.label', 'Livrare pe zone'),
        ));

        $this->app->singleton(SamedayProvider::class, fn (): SamedayProvider => new SamedayProvider(
            (string) config('shipping.sameday.username', ''),
            (string) config('shipping.sameday.password', ''),
            (bool) config('shipping.sameday.sandbox', true),
            (string) config('shipping.sameday.base_url', 'https://sameday-api.demo.zitec.com'),
            (string) config('shipping.sameday.label', 'Sameday (curier)'),
        ));

        $this->app->singleton(CargusProvider::class, fn (): CargusProvider => new CargusProvider(
            (string) config('shipping.cargus.subscription_key', ''),
            (string) config('shipping.cargus.username', ''),
            (string) config('shipping.cargus.password', ''),
            (bool) config('shipping.cargus.sandbox', true),
            (string) config('shipping.cargus.base_url', 'https://urgentcargus.azure-api.net/api'),
            (string) config('shipping.cargus.label', 'Cargus (curier)'),
        ));
    }

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
    ];
}
