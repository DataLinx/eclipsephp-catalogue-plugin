<?php

namespace Eclipse\Catalogue\Enums;

use Filament\Support\Contracts\HasLabel;

enum BackgroundType: string implements HasLabel
{
    case NONE = 'n';
    case SOLID = 's';
    case GRADIENT = 'g';
    case MULTICOLOR = 'm';

    /**
     * Get the label for the background type.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::NONE => 'None',
            self::SOLID => 'Solid',
            self::GRADIENT => 'Gradient',
            self::MULTICOLOR => 'Multicolor',
        };
    }
}
