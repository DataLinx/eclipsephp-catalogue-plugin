<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Traits\HandlesTenantData;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Eclipse\Core\Models\Locale;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EditProduct extends EditRecord
{
    use HandlesTenantData, HasTenantFields;
    use Translatable;
    use WithRecordNavigation;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviousRecordAction::make(),
            NextRecordAction::make(),
            LocaleSwitcher::make(),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Hydrate property values for the product
        $data = $this->hydratePropertyFields($data);

        // Hydrate tenant-scoped fields
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            $recordData = $this->record->productData()->first();
            if ($recordData) {
                $data['is_active'] = $recordData->is_active;
                $data['has_free_delivery'] = $recordData->has_free_delivery;
                $data['available_from_date'] = $recordData->available_from_date;
                $data['sorting_label'] = $recordData->sorting_label;
                $data['category_id'] = $recordData->category_id ?? null;
                $data['product_status_id'] = $recordData->product_status_id ?? null;
                $data['stock'] = $recordData->stock;
                $data['min_stock'] = $recordData->min_stock;
                $data['date_stocked'] = $recordData->date_stocked;
            }

            $data['groups'] = $this->record->groups()->pluck('pim_group.id')->toArray();

            return $data;
        }

        $tenantData = $this->buildTenantDataPayload($tenantFK);

        $data['tenant_data'] = $tenantData;
        $data['all_tenant_data'] = $tenantData;
        $currentTenant = Filament::getTenant();
        $data['selected_tenant'] = $currentTenant?->id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'property_values_') || str_starts_with($key, 'custom_property_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record) {
            $state = $this->form->getRawState();
            $this->syncListPropertyValues($state);
            $this->syncCustomPropertyValues($state);
        }
    }

    /**
     * Build per-tenant payload for all tenants to prefill the form.
     */
    private function buildTenantDataPayload(string $tenantFK): array
    {
        $tenantData = [];
        $dataRecords = $this->record->productData()->get();

        // Prefetch groups per tenant in one query by mapping group_id -> site_id
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
        $groupsByTenant = $this->record->groups()
            ->select('pim_group.id', 'pim_group.'.$tenantFK)
            ->get()
            ->groupBy($tenantFK)
            ->map(fn ($rows) => $rows->pluck('id')->toArray())
            ->toArray();

        foreach ($dataRecords as $tenantRecord) {
            $tenantId = $tenantRecord->getAttribute($tenantFK);
            $tenantData[$tenantId] = [
                'is_active' => $tenantRecord->is_active,
                'has_free_delivery' => $tenantRecord->has_free_delivery,
                'available_from_date' => $tenantRecord->available_from_date,
                'sorting_label' => $tenantRecord->sorting_label,
                'category_id' => $tenantRecord->category_id ?? null,
                'product_status_id' => $tenantRecord->product_status_id ?? null,
                'stock' => $tenantRecord->stock,
                'min_stock' => $tenantRecord->min_stock,
                'date_stocked' => $tenantRecord->date_stocked,
                'groups' => $groupsByTenant[$tenantId] ?? [],
            ];
        }

        return $tenantData;
    }

    /**
     * Populate property_values_* and custom_property_* into the provided data array.
     */
    private function hydratePropertyFields(array $data): array
    {
        if (! ($this->record && $this->record->product_type_id)) {
            return $data;
        }

        $properties = Property::where('is_active', true)
            ->where(function ($query) {
                $query->where('is_global', true)
                    ->orWhereHas('productTypes', function ($q) {
                        $q->where('pim_product_types.id', $this->record->product_type_id);
                    });
            })
            ->get();

        // Prefetch all selected list property values for this product in one query
        $selectedValuesByProperty = $this->record->propertyValues()
            ->select('pim_property_value.id', 'pim_property_value.property_id')
            ->get()
            ->groupBy('property_id')
            ->map(fn ($rows) => $rows->pluck('id')->toArray())
            ->toArray();

        // Prefetch all custom property values in one query
        $customValuesByProperty = $this->record->customPropertyValues()
            ->get()
            ->keyBy('property_id');

        foreach ($properties as $property) {
            if ($property->isListType() || $property->isColorType()) {
                $fieldName = "property_values_{$property->id}";
                $selectedValues = $selectedValuesByProperty[$property->id] ?? [];
                $data[$fieldName] = ($property->max_values === 1)
                    ? ($selectedValues[0] ?? null)
                    : $selectedValues;
            } else {
                $fieldName = "custom_property_{$property->id}";
                $customValue = $customValuesByProperty->get($property->id);
                if ($customValue) {
                    $data[$fieldName] = $customValue->value;
                } else {
                    if ($property->supportsMultilang()) {
                        $locales = $this->getAvailableLocales();
                        $data[$fieldName] = array_fill_keys($locales, '');
                    } else {
                        $data[$fieldName] = null;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Sync many-to-many list property values based on form state.
     */
    private function syncListPropertyValues(array $state): void
    {
        $propertyData = [];
        foreach ($state as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'property_values_')) {
                $propertyId = str_replace('property_values_', '', $key);
                $propertyData[$propertyId] = $value;
            }
        }

        // Detach all existing property values in a single operation to avoid repeated queries
        $allCurrentIds = \Eclipse\Catalogue\Models\PropertyValue::query()->pluck('id')->all();
        if (! empty($allCurrentIds)) {
            $this->record->propertyValues()->detach($allCurrentIds);
        }

        // Attach back the new selections per property
        foreach ($propertyData as $propertyId => $values) {
            if (empty($values)) {
                continue;
            }
            $valuesToAttach = is_array($values) ? $values : [$values];
            $valuesToAttach = array_filter($valuesToAttach);
            if (! empty($valuesToAttach)) {
                $this->record->propertyValues()->attach($valuesToAttach);
            }
        }
    }

    /**
     * Upsert custom property single-value fields based on form state.
     */
    private function syncCustomPropertyValues(array $state): void
    {
        $customPropertyData = [];
        foreach ($state as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'custom_property_')) {
                $propertyId = str_replace('custom_property_', '', $key);
                $customPropertyData[$propertyId] = $value;
            }
        }

        foreach ($customPropertyData as $propertyId => $value) {
            $property = Property::find($propertyId);
            if ($property && $property->isCustomType()) {
                if ($property->supportsMultilang() && is_array($value)) {
                    $filteredValue = array_filter($value, fn ($v) => $v !== null && $v !== '');
                    if (! empty($filteredValue)) {
                        $this->record->setCustomPropertyValue($property, $value);
                    } else {
                        $this->record->customPropertyValues()
                            ->where('property_id', $propertyId)
                            ->delete();
                    }
                } elseif ($value !== null && $value !== '') {
                    $this->record->setCustomPropertyValue($property, $value);
                } else {
                    $this->record->customPropertyValues()
                        ->where('property_id', $propertyId)
                        ->delete();
                }
            }
        }
    }

    protected function getFormTenantFlags(): array
    {
        return ['is_active', 'has_free_delivery'];
    }

    protected function getFormMutuallyExclusiveFlagSets(): array
    {
        return [];
    }

    public function schema(Schema $schema): Schema
    {
        return $schema;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->action(function () {
                    $this->storeCurrentTenantData();
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->save();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $tenantData = $this->extractTenantDataFromFormData($data);
        $mainData = $this->cleanFormDataForMainRecord($data);

        $record->updateWithTenantData($mainData, $tenantData);

        // Sync groups via Group model methods (weak pivot handling) using per-tenant selections
        $state = $this->form->getState();

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        if ($tenantFK) {
            $desiredGroupIds = collect($tenantData)
                ->flatMap(fn ($td) => array_map('intval', (array) ($td['groups'] ?? [])))
                ->unique()
                ->values()
                ->toArray();
        } else {
            $desiredGroupIds = array_values(array_unique(array_map('intval', (array) ($state['groups'] ?? []))));
        }

        $currentGroupIds = $record->groups()->pluck('pim_group.id')->map(fn ($id) => (int) $id)->toArray();
        $toAttach = array_values(array_diff($desiredGroupIds, $currentGroupIds));
        $toDetach = array_values(array_diff($currentGroupIds, $desiredGroupIds));

        foreach ($toAttach as $groupId) {
            $group = Group::find($groupId);
            if ($group) {
                $group->addProduct($record);
            }
        }

        foreach ($toDetach as $groupId) {
            $group = Group::find($groupId);
            if ($group) {
                $group->removeProduct($record);
            }
        }

        return $record;
    }

    /**
     * Override the getRecordUrl method to navigate to edit pages instead of view pages
     */
    protected function getRecordUrl(Model $record): string
    {
        return static::getResource()::getUrl('edit', ['record' => $record]);
    }

    /**
     * Get available locales for the application.
     */
    protected function getAvailableLocales(): array
    {
        if (class_exists(Locale::class)) {
            return Locale::getAvailableLocales()->pluck('id')->toArray();
        }

        return ['en'];
    }

    public function reorderImages(string $statePath, array $uuids): void
    {
        if (! $this->record) {
            return;
        }

        $mediaItems = $this->record->getMedia('images');
        $uuidToId = $mediaItems->pluck('id', 'uuid')->toArray();

        $orderedIds = collect($uuids)
            ->map(fn ($uuid) => $uuidToId[$uuid] ?? null)
            ->filter()
            ->toArray();

        if (! empty($orderedIds)) {
            $mediaClass = config('media-library.media_model', Media::class);
            $mediaClass::setNewOrder($orderedIds);
        }

        $this->data['images'] = $this->record->getMedia('images')
            ->sortBy('order_column')
            ->map(fn ($media) => [
                'id' => $media->id,
                'uuid' => $media->uuid,
                'url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
                'preview_url' => $media->getUrl('preview'),
                'name' => $media->getCustomProperty('name', []),
                'description' => $media->getCustomProperty('description', []),
                'is_cover' => $media->getCustomProperty('is_cover', false),
                'order_column' => $media->order_column,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ])
            ->values()
            ->toArray();
    }
}
