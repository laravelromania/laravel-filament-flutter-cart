<?php

namespace Modules\Payments\Providers;

use Modules\Checkout\Services\PaymentManager;
use Modules\Payments\Drivers\NetopiaProvider;
use Modules\Payments\Drivers\PayuProvider;
use Nwidart\Modules\Support\ModuleServiceProvider;

/**
 * Wires the Payments module into the store the same way Shipping did (Part 10):
 * `register()` builds each driver from `config/config.php` (fed by `.env`), and
 * `boot()` resolves Checkout's {@see PaymentManager} SINGLETON — the registry the
 * checkout wizard reads — and registers the real drivers into it. boot() runs
 * after every module's register(), so the singleton already exists; we extend it,
 * never re-bind it. The Part-8 mock stays; Netopia and PayU appear at checkout on
 * their own, without Checkout ever depending on Payments.
 */
class PaymentsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Payments';

    protected string $nameLower = 'payments';

    /**
     * The driver classes registered into the PaymentManager, in display order.
     *
     * @var array<int, class-string<\Modules\Core\Contracts\PaymentGateway>>
     */
    private const DRIVERS = [
        NetopiaProvider::class,
        PayuProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->registerDrivers();
    }

    public function boot(): void
    {
        parent::boot();

        // Extend the SAME singleton Checkout created in Part 8 — never re-bind it.
        $manager = $this->app->make(PaymentManager::class);

        foreach (self::DRIVERS as $driver) {
            $manager->register($this->app->make($driver));
        }
    }

    /**
     * Bind each driver as a singleton built from config. The closures resolve
     * lazily — first hit is boot() above, by which point config is merged.
     */
    private function registerDrivers(): void
    {
        $this->app->singleton(NetopiaProvider::class, fn (): NetopiaProvider => new NetopiaProvider(
            (string) config('payments.netopia.signature', ''),
            (string) config('payments.netopia.public_cert', ''),
            (string) config('payments.netopia.private_key', ''),
            (bool) config('payments.netopia.sandbox', true),
            (string) config('payments.netopia.base_url', ''),
            (string) config('payments.netopia.label', 'Card bancar (Netopia)'),
            (string) config('payments.sandbox_secret', 'sandbox-secret-key'),
        ));

        $this->app->singleton(PayuProvider::class, fn (): PayuProvider => new PayuProvider(
            (string) config('payments.payu.merchant', ''),
            (string) config('payments.payu.pos_id', ''),
            (string) config('payments.payu.secret', ''),
            (bool) config('payments.payu.sandbox', true),
            (string) config('payments.payu.base_url', ''),
            (string) config('payments.payu.label', 'Card bancar (PayU)'),
            (string) config('payments.sandbox_secret', 'sandbox-secret-key'),
        ));
    }

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
