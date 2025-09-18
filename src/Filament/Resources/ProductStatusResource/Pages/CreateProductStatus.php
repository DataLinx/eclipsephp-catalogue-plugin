<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductStatusResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateProductStatus extends CreateRecord
{
    use Translatable;

    protected static string $resource = ProductStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentTenant = \Filament\Facades\Filament::getTenant();
        if ($currentTenant) {
            $data['site_id'] = $currentTenant->id;
        }

        if (($data['allow_price_display'] ?? true) === false) {
            $data['allow_sale'] = false;
        }

        if (! empty($data['is_default'])) {
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
            $query = \Eclipse\Catalogue\Models\ProductStatus::query()->where('is_default', true);
            if ($tenantFK && isset($data[$tenantFK])) {
                $query->where($tenantFK, $data[$tenantFK]);
            }
            $query->update(['is_default' => false]);
        }

        return $data;
    }
}
