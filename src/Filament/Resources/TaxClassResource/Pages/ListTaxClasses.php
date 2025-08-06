<?php

namespace Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages;

use Eclipse\Catalogue\Filament\Resources\TaxClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxClasses extends ListRecords
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
