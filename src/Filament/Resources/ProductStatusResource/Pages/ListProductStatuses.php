<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductStatusResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;

class ListProductStatuses extends ListRecords
{
    use Translatable;

    protected static string $resource = ProductStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make()
                ->label(__('eclipse-catalogue::product-status.actions.create')),
        ];
    }
}
