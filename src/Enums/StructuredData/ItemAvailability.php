<?php

namespace Eclipse\Catalogue\Enums\StructuredData;

use Filament\Support\Contracts\HasLabel;

enum ItemAvailability: string implements HasLabel
{
    case DISCONTINUED = 'Discontinued';
    case IN_STOCK = 'InStock';
    case IN_STORE_ONLY = 'InStoreOnly';
    case LIMITED_AVAILABILITY = 'LimitedAvailability';
    case ONLINE_ONLY = 'OnlineOnly';
    case OUT_OF_STOCK = 'OutOfStock';
    case PREORDER = 'PreOrder';
    case PRESALE = 'PreSale';
    case SOLD_OUT = 'SoldOut';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DISCONTINUED => 'Discontinued - Indicates that the item has been discontinued.',
            self::IN_STOCK => 'InStock - Indicates that the item is in stock.',
            self::IN_STORE_ONLY => 'InStoreOnly - Indicates that the item is available only at physical locations.',
            self::LIMITED_AVAILABILITY => 'LimitedAvailability - Indicates that the item has limited availability.',
            self::ONLINE_ONLY => 'OnlineOnly - Indicates that the item is available only online.',
            self::OUT_OF_STOCK => 'OutOfStock - Indicates that the item is out of stock.',
            self::PREORDER => 'PreOrder - Indicates that the item is available for pre-order, but will be delivered when generally available.',
            self::PRESALE => 'PreSale - Indicates that the item is available for ordering and delivery before general availability.',
            self::SOLD_OUT => 'SoldOut - Indicates that the item has sold out.',
        };
    }
}
