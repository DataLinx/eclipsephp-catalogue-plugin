<?php

namespace Eclipse\Catalogue\Filament\Resources\MeasureUnitResource\Pages;

use Eclipse\Catalogue\Filament\Resources\MeasureUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMeasureUnits extends ListRecords
{
    protected static string $resource = MeasureUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
