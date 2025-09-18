<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyResource\Pages;

use Eclipse\Catalogue\Enums\PropertyType;
use Eclipse\Catalogue\Filament\Resources\PropertyResource;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\RelationManagers;
use Filament\Actions;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditProperty extends EditRecord
{
    use Translatable;

    protected static string $resource = PropertyResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        $managers = [];

        if ($this->getRecord() && $this->getRecord()->type === PropertyType::LIST->value) {
            $managers[] = RelationManagers\ValuesRelationManager::class;
        }

        return $managers;
    }
}
