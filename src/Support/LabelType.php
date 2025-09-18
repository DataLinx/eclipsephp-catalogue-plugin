<?php

namespace Eclipse\Catalogue\Support;

use Filament\Support\Facades\FilamentColor;

/**
 * Generic helper to resolve a label CSS class from a configured color name.
 * Keeps options in sync with Filament's theme colors.
 */
class LabelType
{
    public static function options(): array
    {
        $colors = FilamentColor::getColors();
        // Return a simple [color => Color Name] map
        $options = [];
        foreach ($colors as $name => $value) {
            $options[$name] = ucfirst(str_replace(['-', '_'], ' ', $name));
        }

        return $options;
    }

    public static function badgeClass(string $color): string
    {
        // Tailwind class used by Filament badges
        return "fi-badge fi-color-{$color}";
    }
}
