<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\Concerns\HandlesImageUploads;
use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Traits\HandlesTenantData;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    use HandlesImageUploads;
    use HandlesTenantData, HasTenantFields;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'property_values_') || str_starts_with($key, 'custom_property_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function getFormTenantFlags(): array
    {
        return ['is_active', 'has_free_delivery'];
    }

    protected function getFormMutuallyExclusiveFlagSets(): array
    {
        return [];
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $tenantData = $this->extractTenantDataFromFormData($data);
        $productData = $this->cleanFormDataForMainRecord($data);

        return Product::createWithTenantData($productData, $tenantData);
    }

    protected function afterCreate(): void
    {
        /** @var Product $product */
        $product = $this->record;

        if (! $product) {
            return;
        }

        $state = $this->form->getState();
        $rawState = $this->form->getRawState();
        $tenantData = $state['tenant_data'] ?? [];

        // Handle property values
        $propertyData = [];
        foreach ($rawState as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'property_values_')) {
                $propertyId = str_replace('property_values_', '', $key);
                $propertyData[$propertyId] = $value;
            }
        }

        foreach ($propertyData as $propertyId => $values) {
            if ($values) {
                $valuesToAttach = is_array($values) ? $values : [$values];
                $valuesToAttach = array_filter($valuesToAttach);

                if (! empty($valuesToAttach)) {
                    $product->propertyValues()->attach($valuesToAttach);
                }
            }
        }

        // Handle custom properties
        $customPropertyData = [];
        foreach ($rawState as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'custom_property_')) {
                $propertyId = str_replace('custom_property_', '', $key);
                $customPropertyData[$propertyId] = $value;
            }
        }

        foreach ($customPropertyData as $propertyId => $value) {
            $property = \Eclipse\Catalogue\Models\Property::find($propertyId);
            if ($property && $property->isCustomType()) {
                if ($property->supportsMultilang() && is_array($value)) {
                    $filteredValue = array_filter($value, fn ($v) => $v !== null && $v !== '');
                    if (! empty($filteredValue)) {
                        $product->setCustomPropertyValue($property, $value);
                    }
                } elseif ($value !== null && $value !== '') {
                    $product->setCustomPropertyValue($property, $value);
                }
            }
        }

        // Handle tenant/group associations
        $isTenancyEnabled = (bool) config('eclipse-catalogue.tenancy.model');
        if ($isTenancyEnabled) {
            foreach ($tenantData as $tenantId => $data) {
                $groupIds = array_filter(array_map('intval', (array) ($data['groups'] ?? [])));
                foreach ($groupIds as $groupId) {
                    $group = \Eclipse\Catalogue\Models\Group::find($groupId);
                    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
                    if ($group && (int) $group->getAttribute($tenantFK) === (int) $tenantId) {
                        $group->addProduct($product);
                    }
                }
            }
        } else {
            $flatGroupIds = [];
            if (isset($state['groups'])) {
                $flatGroupIds = array_filter(array_map('intval', (array) $state['groups']));
            } else {
                foreach ($tenantData as $data) {
                    foreach ((array) ($data['groups'] ?? []) as $id) {
                        $flatGroupIds[] = (int) $id;
                    }
                }
                $flatGroupIds = array_values(array_unique(array_filter($flatGroupIds)));
            }

            foreach ($flatGroupIds as $groupId) {
                if ($group = \Eclipse\Catalogue\Models\Group::find($groupId)) {
                    $group->addProduct($product);
                }
            }
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->action(function () {
                    $this->storeCurrentTenantData();
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }
}
