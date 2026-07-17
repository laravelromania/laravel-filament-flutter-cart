<?php

declare(strict_types=1);

namespace Modules\Api\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * The Api module is JSON-only — it exposes no `web` routes and no views. Its
 * route file lives at `routes/api.php`; here it is loaded with the framework's
 * `api` middleware group and the `/api` prefix, while the file itself adds the
 * `v1` version segment, so every endpoint resolves under `/api/v1/...`.
 */
class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Api';

    public function map(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->name('api.')
            ->group(module_path($this->name, '/routes/api.php'));
    }
}
