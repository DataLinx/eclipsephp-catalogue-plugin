<?php

namespace Eclipse\Catalogue;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CatalogueServiceProvider extends PackageServiceProvider
{
    public static string $name = 'eclipse-catalogue';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->discoversMigrations()
            ->runsMigrations();
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
}
