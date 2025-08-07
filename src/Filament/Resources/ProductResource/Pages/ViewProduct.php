<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;

class ViewProduct extends ViewRecord
{
    use ViewRecord\Concerns\Translatable;
    use WithRecordNavigation;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviousRecordAction::make(),
            NextRecordAction::make(),
            Actions\LocaleSwitcher::make(),
            Actions\EditAction::make(),
        ];
    }
}
