<?php

namespace Eclipse\Catalogue;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CataloguePlugin implements Plugin
{
    public function getId(): string
    {
        return 'eclipse-catalogue';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(__DIR__.'/Filament/Resources', 'Eclipse\\Catalogue\\Filament\\Resources');
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
