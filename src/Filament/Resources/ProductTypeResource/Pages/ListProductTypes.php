<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductTypeResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductTypeResource;
use Filament\Actions\CreateAction;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\ListRecords;

class ListProductTypes extends ListRecords
{
    use Translatable;

    protected static string $resource = ProductTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
