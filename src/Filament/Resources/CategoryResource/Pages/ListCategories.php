<?php

namespace Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages;

use Eclipse\Catalogue\Filament\Resources\CategoryResource;
use Eclipse\Common\Foundation\Pages\HasScoutSearch;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;

class ListCategories extends ListRecords
{
    use HasScoutSearch, Translatable;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
