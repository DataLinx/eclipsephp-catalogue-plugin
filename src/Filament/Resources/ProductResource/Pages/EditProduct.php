<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EditProduct extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    use WithRecordNavigation;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreviousRecordAction::make(),
            NextRecordAction::make(),
            Actions\LocaleSwitcher::make(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load property values for the product
        if ($this->record && $this->record->product_type_id) {
            $properties = Property::where('is_active', true)
                ->where(function ($query) {
                    $query->where('is_global', true)
                        ->orWhereHas('productTypes', function ($q) {
                            $q->where('pim_product_types.id', $this->record->product_type_id);
                        });
                })
                ->get();

            foreach ($properties as $property) {
                $fieldName = "property_values_{$property->id}";
                $selectedValues = $this->record->propertyValues()
                    ->where('pim_property_value.property_id', $property->id)
                    ->pluck('pim_property_value.id')
                    ->toArray();

                if ($property->max_values === 1) {
                    $data[$fieldName] = $selectedValues[0] ?? null;
                } else {
                    $data[$fieldName] = $selectedValues;
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'property_values_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record) {
            $state = $this->form->getRawState();
            $propertyData = [];
            foreach ($state as $key => $value) {
                if (is_string($key) && str_starts_with($key, 'property_values_')) {
                    $propertyId = str_replace('property_values_', '', $key);
                    $propertyData[$propertyId] = $value;
                }
            }

            foreach ($propertyData as $propertyId => $values) {
                $idsToDetach = \Eclipse\Catalogue\Models\PropertyValue::query()
                    ->where('property_id', $propertyId)
                    ->pluck('id')
                    ->all();

                if (! empty($idsToDetach)) {
                    $this->record->propertyValues()->detach($idsToDetach);
                }

                // Add new values
                if ($values) {
                    $valuesToAttach = is_array($values) ? $values : [$values];
                    $valuesToAttach = array_filter($valuesToAttach); // Remove null values

                    if (! empty($valuesToAttach)) {
                        $this->record->propertyValues()->attach($valuesToAttach);
                    }
                }
            }
        }
    }

    /**
     * Override the getRecordUrl method to navigate to edit pages instead of view pages
     */
    protected function getRecordUrl(Model $record): string
    {
        return static::getResource()::getUrl('edit', ['record' => $record]);
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
