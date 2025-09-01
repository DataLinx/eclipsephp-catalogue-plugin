<?php

namespace Eclipse\Catalogue\Enums;

enum PropertyType: string
{
    case LIST = 'list';
    case CUSTOM = 'custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::LIST => 'List (Predefined Values)',
            self::CUSTOM => 'Custom (User Input)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (PropertyType $enum) => [$enum->value => $enum->getLabel()])
            ->toArray();
    }
}
