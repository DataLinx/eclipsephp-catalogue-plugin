<?php

namespace Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages;

use Eclipse\Catalogue\Filament\Resources\TaxClassResource;
use Eclipse\Catalogue\Models\TaxClass;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxClass extends CreateRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If this class is being set as default, unset all other defaults within the same tenant
        if ($data['is_default'] ?? false) {
            $query = TaxClass::where('is_default', true);

            // Add tenant scope if tenancy is configured
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
            $tenantId = Filament::getTenant()?->id;
            if ($tenantFK && $tenantId) {
                $query->where($tenantFK, $tenantId);
            }

            $query->update(['is_default' => false]);
        }

        // Auto-set current tenant_id if tenancy is configured
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantId = Filament::getTenant()?->id;
        if ($tenantFK && $tenantId) {
            $data[$tenantFK] = $tenantId;
        } elseif ($tenantFK) {
            throw new \Exception('Current tenant not available. Cannot create TaxClass without tenant context.');
        }

        return $data;
    }
}
