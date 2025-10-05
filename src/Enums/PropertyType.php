<?php

namespace Eclipse\Catalogue\Enums;

enum PropertyType: string
{
    case LIST = 'list';
    case COLOR = 'color';
    case CUSTOM = 'custom';

    /**
     * Get the label for the property type.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::LIST => 'List (Predefined Values)',
            self::COLOR => 'Color (Predefined with color swatch)',
            self::CUSTOM => 'Custom (User Input)',
        };
    }
}
