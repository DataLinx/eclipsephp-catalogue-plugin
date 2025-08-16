<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
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
