<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Nwidart\Modules\Facades\Module;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        // Descoperă componentele Filament din fiecare modul activ. Convenția:
        //   cod în  Modules/<N>/app/Filament/{Resources,Pages,Widgets}
        //   namespace  Modules\<N>\Filament\{Resources,Pages,Widgets}
        // Filament sare peste directoarele inexistente, deci un modul fără
        // panou (ex. Catalog în această parte) nu strică boot-ul.
        foreach (Module::allEnabled() as $module) {
            $name = $module->getName();

            $panel
                ->discoverResources(
                    in: base_path("Modules/{$name}/app/Filament/Resources"),
                    for: "Modules\\{$name}\\Filament\\Resources",
                )
                ->discoverPages(
                    in: base_path("Modules/{$name}/app/Filament/Pages"),
                    for: "Modules\\{$name}\\Filament\\Pages",
                )
                ->discoverWidgets(
                    in: base_path("Modules/{$name}/app/Filament/Widgets"),
                    for: "Modules\\{$name}\\Filament\\Widgets",
                );
        }

        return $panel;
    }
}
