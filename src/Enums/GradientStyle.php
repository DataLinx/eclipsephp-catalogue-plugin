<?php

namespace Eclipse\Catalogue\Enums;

use Filament\Support\Contracts\HasLabel;

enum GradientStyle: string implements HasLabel
{
    case SHARP = 's';
    case SOFT = 'f';

    /**
     * Get the label for the gradient style.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::SHARP => 'Sharp',
            self::SOFT => 'Soft',
        };
    }
}
