<?php

namespace Eclipse\Catalogue\Filament\Resources\MeasureUnitResource\Pages;

use Eclipse\Catalogue\Filament\Resources\MeasureUnitResource;
use Eclipse\Catalogue\Models\MeasureUnit;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMeasureUnit extends EditRecord
{
    protected static string $resource = MeasureUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If this unit is being set as default, unset all other defaults
        if ($data['is_default'] ?? false) {
            MeasureUnit::where('is_default', true)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
