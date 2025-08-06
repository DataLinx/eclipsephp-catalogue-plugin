<?php

namespace Eclipse\Catalogue\Filament\Resources\MeasureUnitResource\Pages;

use Eclipse\Catalogue\Filament\Resources\MeasureUnitResource;
use Eclipse\Catalogue\Models\MeasureUnit;
use Filament\Resources\Pages\CreateRecord;

class CreateMeasureUnit extends CreateRecord
{
    protected static string $resource = MeasureUnitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If this unit is being set as default, unset all other defaults
        if ($data['is_default'] ?? false) {
            MeasureUnit::where('is_default', true)->update(['is_default' => false]);
        }

        return $data;
    }
}
