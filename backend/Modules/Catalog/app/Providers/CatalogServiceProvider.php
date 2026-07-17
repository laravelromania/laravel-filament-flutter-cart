<?php

namespace Modules\Catalog\Providers;

use Livewire\Livewire;
use Modules\Catalog\Livewire\CategoryShow;
use Modules\Catalog\Livewire\ProductIndex;
use Modules\Catalog\Livewire\ProductShow;
use Modules\Catalog\Livewire\SearchBox;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CatalogServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Catalog';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'catalog';

    /**
     * Register the storefront Livewire components by name. Full-page components
     * are also routed by class, but naming them lets the shared layout embed the
     * header search box (`@livewire('catalog.search-box')`) without Core having
     * to know about a concrete Catalog class.
     */
    public function boot(): void
    {
        parent::boot();

        Livewire::component('catalog.product-index', ProductIndex::class);
        Livewire::component('catalog.product-show', ProductShow::class);
        Livewire::component('catalog.category-show', CategoryShow::class);
        Livewire::component('catalog.search-box', SearchBox::class);
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
