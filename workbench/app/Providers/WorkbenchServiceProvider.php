<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Workbench\App\Models\Site;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(AdminPanelProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        config([
            'eclipse-catalogue.tenancy.model' => Site::class,
            'eclipse-catalogue.tenancy.foreign_key' => 'site_id',
        ]);
    }
}
