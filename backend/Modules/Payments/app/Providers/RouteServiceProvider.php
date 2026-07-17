<?php

namespace Modules\Payments\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Payments';

    public function map(): void
    {
        $this->mapWebRoutes();
    }

    /**
     * The Payments routes live under `web` so the sandbox simulator and the
     * browser-return page have a session. The callback (IPN) is CSRF-exempt via
     * bootstrap/app.php — a gateway posting server-to-server has no CSRF token.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }
}
