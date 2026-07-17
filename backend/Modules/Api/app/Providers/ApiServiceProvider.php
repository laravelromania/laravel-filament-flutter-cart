<?php

declare(strict_types=1);

namespace Modules\Api\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Nwidart\Modules\Support\ModuleServiceProvider;

/**
 * The Api module is the composition/adapter layer for the Flutter app: it wires
 * the JSON endpoints under `/api/v1` and depends on every business module
 * (Catalog, Cart, Checkout, Orders, Customers) plus Core. It owns no domain
 * logic — it translates HTTP into calls on those modules and their DTOs into
 * JSON resources.
 */
class ApiServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Api';

    protected string $nameLower = 'api';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->registerRateLimiters();
    }

    /**
     * The named limiters the `/api/v1` routes lean on. Laravel 11+ ships no `api`
     * limiter by default, so the Api module defines its own: a generous per-user
     * (or per-IP for public catalog reads) budget for the group, and a deliberately
     * tight per-IP budget for the credential endpoints (`login`/`register`) to
     * blunt brute-force and mass-signup abuse.
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?? $request->ip()));

        RateLimiter::for('auth', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($request->ip()));
    }
}
