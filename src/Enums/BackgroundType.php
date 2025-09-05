<?php

namespace Eclipse\Catalogue\Enums;

enum BackgroundType: string
{
    case NONE = 'n';
    case SOLID = 's';
    case GRADIENT = 'g';
    case MULTICOLOR = 'm';

    /**
     * Get the label for the background type.
     */
    public function label(): string
    {
        return match ($this) {
            self::NONE => 'None',
            self::SOLID => 'Solid',
            self::GRADIENT => 'Gradient',
            self::MULTICOLOR => 'Multicolor',
        };
    }
}
