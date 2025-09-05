<?php

namespace Eclipse\Catalogue\Enums;

enum GradientStyle: string
{
    case SHARP = 's';
    case SOFT = 'f';

    /**
     * Get the label for the gradient style.
     */
    public function label(): string
    {
        return match ($this) {
            self::SHARP => 'Sharp',
            self::SOFT => 'Soft',
        };
    }
}
