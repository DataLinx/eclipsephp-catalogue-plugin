<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Traits\HandlesTenantData;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Nben\FilamentRecordNav\Actions\NextRecordAction;
use Nben\FilamentRecordNav\Actions\PreviousRecordAction;
use Nben\FilamentRecordNav\Concerns\WithRecordNavigation;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EditProduct extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    use HandlesTenantData, HasTenantFields;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            $recordData = $this->record->productData()->first();
            if ($recordData) {
                $data['is_active'] = $recordData->is_active;
                $data['has_free_delivery'] = $recordData->has_free_delivery;
                $data['available_from_date'] = $recordData->available_from_date;
                $data['sorting_label'] = $recordData->sorting_label;
            }

            return $data;
        }

        $tenantData = [];
        $dataRecords = $this->record->productData;

        foreach ($dataRecords as $tenantRecord) {
            $tenantId = $tenantRecord->getAttribute($tenantFK);
            $tenantData[$tenantId] = [
                'is_active' => $tenantRecord->is_active,
                'has_free_delivery' => $tenantRecord->has_free_delivery,
                'available_from_date' => $tenantRecord->available_from_date,
                'sorting_label' => $tenantRecord->sorting_label,
            ];
        }

        $data['tenant_data'] = $tenantData;
        $currentTenant = \Filament\Facades\Filament::getTenant();
        $data['selected_tenant'] = $currentTenant?->id;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $tenantData = $this->extractTenantDataFromFormData($data);
        $mainData = $this->cleanFormDataForMainRecord($data);

        $record->updateWithTenantData($mainData, $tenantData);

        return $record;
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
