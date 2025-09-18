<?php

namespace Eclipse\Catalogue\Enums;

enum PropertyInputType: string
{
    case STRING = 'string';
    case TEXT = 'text';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case FILE = 'file';

    public function getLabel(): string
    {
        return match ($this) {
            self::STRING => 'String (up to 255 characters)',
            self::TEXT => 'Text',
            self::INTEGER => 'Integer',
            self::DECIMAL => 'Decimal',
            self::DATE => 'Date',
            self::DATETIME => 'Date & Time',
            self::FILE => 'File',
        };
    }

    public function supportsMultilang(): bool
    {
        return in_array($this, [self::STRING, self::TEXT, self::FILE]);
    }
}
