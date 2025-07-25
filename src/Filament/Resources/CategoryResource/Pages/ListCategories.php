<?php

namespace Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages;

use Eclipse\Catalogue\Filament\Resources\CategoryResource;
use Eclipse\Common\Foundation\Pages\HasScoutSearch;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Filament\Support\Enums\MaxWidth;

class ListCategories extends ListRecords
{
    use HasScoutSearch, Translatable;

    protected static string $resource = CategoryResource::class;

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\Action::make('sorting')
                ->label(__('eclipse-catalogue::categories.actions.sorting'))
                ->icon('heroicon-o-arrows-up-down')
                ->color('gray')
                ->url(fn () => self::$resource::getUrl('sorting')),
            Actions\CreateAction::make()
                ->label(__('eclipse-catalogue::categories.actions.create'))
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
