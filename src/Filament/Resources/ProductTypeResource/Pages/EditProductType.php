<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductTypeResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductTypeResource;
use Eclipse\Catalogue\Traits\HandlesTenantData;
use Eclipse\Catalogue\Traits\HasProductTypeForm;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProductType extends EditRecord
{
    use HandlesTenantData, HasProductTypeForm, HasTenantFields, Translatable;

    protected static string $resource = ProductTypeResource::class;

    protected function getFormTenantFlags(): array
    {
        return ['is_active', 'is_default'];
    }

    protected function getFormMutuallyExclusiveFlagSets(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->buildProductTypeFormSchema());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - load single record
            $typeData = $this->record->productTypeData()->first();

            if ($typeData) {
                $data['is_active'] = $typeData->is_active;
                $data['is_default'] = $typeData->is_default;
            }

            return $data;
        }

        // Load tenant-specific data
        $tenantData = [];
        $typeDataRecords = $this->record->productTypeData;

        foreach ($typeDataRecords as $tenantRecord) {
            $tenantId = $tenantRecord->getAttribute($tenantFK);
            $tenantData[$tenantId] = [
                'is_active' => $tenantRecord->is_active,
                'is_default' => $tenantRecord->is_default,
            ];
        }

        $data['tenant_data'] = $tenantData;

        // Set the selected tenant to current tenant so the form shows properly
        $currentTenant = \Filament\Facades\Filament::getTenant();
        $data['selected_tenant'] = $currentTenant?->id;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Extract tenant data from form
        $tenantData = $this->extractTenantDataFromFormData($data);

        // Clean main record data
        $typeData = $this->cleanFormDataForMainRecord($data);

        // Use the model's updateWithTenantData method
        $record->updateWithTenantData($typeData, $tenantData);

        return $record;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->action(function () {
                    // Store current tenant data before validation/saving
                    $this->storeCurrentTenantData();
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->save();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function resolveRecord($key): Model
    {
        // Load record without joins to avoid ambiguous column issues
        return static::getResource()::getModel()::withoutGlobalScopes()->findOrFail($key);
    }
}
