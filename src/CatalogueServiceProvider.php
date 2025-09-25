<?php

namespace Eclipse\Catalogue;

use Eclipse\Catalogue\Livewire\TenantSwitcher;
use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CatalogueServiceProvider extends PackageServiceProvider
{
    public static string $name = 'eclipse-catalogue';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasAssets();
    }

    public function register()
    {
        parent::register();

        $settings = Config::get('scout.typesense.model-settings', []);

        $settings += [
            Product::class => Product::getTypesenseSettings(),
            Category::class => Category::getTypesenseSettings(),
        ];

        Config::set('scout.typesense.model-settings', $settings);
    }

    public function boot()
    {
        parent::boot();

        // Register Livewire components
        if (class_exists(Livewire::class)) {
            Livewire::component('eclipse-catalogue::tenant-switcher', TenantSwitcher::class);
        }

        FilamentAsset::register([
            Css::make('eclipse-catalogue', __DIR__.'/../resources/css/catalogue.css'),
        ], package: static::$name);
    }
}
