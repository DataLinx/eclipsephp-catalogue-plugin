<?php

namespace Eclipse\Catalogue\Enums;

enum GradientDirection: string
{
    case RIGHT = 'right';
    case LEFT = 'left';
    case TOP = 'top';
    case BOTTOM = 'bottom';

    /**
     * Get the label for the gradient direction.
     */
    public function label(): string
    {
        return match ($this) {
            self::RIGHT => 'Right',
            self::LEFT => 'Left',
            self::TOP => 'Top',
            self::BOTTOM => 'Bottom',
        };
    }
}
