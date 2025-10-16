<?php

namespace Eclipse\Catalogue\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductRelationType: string implements HasLabel
{
    case RELATED = 'related';
    case CROSS_SELL = 'cross_sell';
    case UPSELL = 'upsell';

    /**
     * Get the label for the product relation type.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::RELATED => 'Related',
            self::CROSS_SELL => 'Cross-sell',
            self::UPSELL => 'Upsell',
        };
    }

    /**
     * Get the description for the product relation type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::RELATED => 'Similar products that customers might be interested in',
            self::CROSS_SELL => 'Complementary products or add-ons that enhance the main product',
            self::UPSELL => 'Higher-priced or premium versions with more features',
        };
    }
}
