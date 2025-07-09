<?php

namespace Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages;

use Eclipse\Catalogue\Filament\Resources\CategoryResource;
use Filament\Actions;
use SolutionForest\FilamentTree\Concern\TreeRecords\Translatable;
use SolutionForest\FilamentTree\Resources\Pages\TreePage as BasePage;

class SortingCategory extends BasePage
{
    use Translatable;

    protected static string $resource = CategoryResource::class;

    protected static int $maxDepth = 6;

    protected function getActions(): array
    {
        return [
            $this->getCreateAction(),
            Actions\LocaleSwitcher::make(),
        ];
    }

    public function getTreeRecordTitle(?\Illuminate\Database\Eloquent\Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        return $record->name;
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    {
        return null;
    }
}
