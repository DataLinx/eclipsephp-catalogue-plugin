<?php

namespace Eclipse\Catalogue;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Config;
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
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('eclipse-catalogue::tenant-switcher', \Eclipse\Catalogue\Livewire\TenantSwitcher::class);
            \Livewire\Livewire::component('eclipse.catalogue.livewire.product-selector-table', \Eclipse\Catalogue\Livewire\ProductSelectorTable::class);
            \Livewire\Livewire::component('eclipse.catalogue.livewire.product-relations-table', \Eclipse\Catalogue\Livewire\ProductRelationsTable::class);
        }

        FilamentAsset::register([
            Css::make('eclipse-catalogue', __DIR__.'/../resources/css/catalogue.css'),
        ], package: static::$name);
    }
}
