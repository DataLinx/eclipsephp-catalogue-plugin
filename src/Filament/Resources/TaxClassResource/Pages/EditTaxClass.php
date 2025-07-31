<?php

namespace Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages;

use Eclipse\Catalogue\Filament\Resources\TaxClassResource;
use Eclipse\Catalogue\Models\TaxClass;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditTaxClass extends EditRecord
{
    protected static string $resource = TaxClassResource::class;

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
        // If this class is being set as default, unset all other defaults within the same tenant
        if ($data['is_default'] ?? false) {
            $query = TaxClass::where('is_default', true)
                ->where('id', '!=', $this->record->id);

            // Add tenant scope if tenancy is configured
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
            $tenantId = $this->record->getAttribute($tenantFK);
            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }

            $query->update(['is_default' => false]);
        }

        // Ensure tenant id is preserved if tenancy is configured
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        if ($tenantFK && ! isset($data[$tenantFK])) {
            // Use current tenant from Filament or preserve existing
            $tenantId = Filament::getTenant()?->id;
            if ($tenantId) {
                $data[$tenantFK] = $tenantId;
            } else {
                $data[$tenantFK] = $this->record->getAttribute($tenantFK);
            }
        }

        return $data;
    }
}
