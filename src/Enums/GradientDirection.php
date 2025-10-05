<?php

namespace Eclipse\Catalogue\Enums;

use Filament\Support\Contracts\HasLabel;

enum GradientDirection: string implements HasLabel
{
    case RIGHT = 'right';
    case LEFT = 'left';
    case TOP = 'top';
    case BOTTOM = 'bottom';

    /**
     * Get the label for the gradient direction.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::RIGHT => 'Right',
            self::LEFT => 'Left',
            self::TOP => 'Top',
            self::BOTTOM => 'Bottom',
        };
    }
}
