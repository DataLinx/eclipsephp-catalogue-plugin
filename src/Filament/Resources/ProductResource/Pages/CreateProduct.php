<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = ProductResource::class;
    
    public ?array $temporaryImages = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store images temporarily and remove from data to avoid mass assignment
        if (isset($data['images'])) {
            $this->temporaryImages = $data['images'];
            unset($data['images']);
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Process temporary images
        $pendingImages = $this->temporaryImages;
        
        if (!empty($pendingImages) && is_array($pendingImages)) {
            foreach ($pendingImages as $index => $item) {
                if (isset($item['temp_file'])) {
                    $tempPath = storage_path('app/public/' . $item['temp_file']);
                    
                    if (file_exists($tempPath)) {
                        $this->record->addMedia($tempPath)
                            ->usingFileName($item['file_name'] ?? basename($tempPath))
                            ->withCustomProperties([
                                'name' => $item['name'] ?? [],
                                'description' => $item['description'] ?? [],
                                'is_cover' => $item['is_cover'] ?? false,
                                'position' => $index,
                            ])
                            ->toMediaCollection('images');
                        
                        @unlink($tempPath);
                    }
                } elseif (isset($item['temp_url'])) {
                    try {
                        $this->record->addMediaFromUrl($item['temp_url'])
                            ->usingFileName($item['file_name'] ?? basename($item['temp_url']))
                            ->withCustomProperties([
                                'name' => $item['name'] ?? [],
                                'description' => $item['description'] ?? [],
                                'is_cover' => $item['is_cover'] ?? false,
                                'position' => $index,
                            ])
                            ->toMediaCollection('images');
                    } catch (\Exception $e) {
                        // Ignore failed URLs
                    }
                }
            }
            
            // Ensure single cover image
            $coverMedia = $this->record->getMedia('images')
                ->filter(fn ($media) => $media->getCustomProperty('is_cover', false));

            if ($coverMedia->count() > 1) {
                $coverMedia->skip(1)->each(function ($media) {
                    $media->setCustomProperty('is_cover', false);
                    $media->save();
                });
            }

            if ($coverMedia->count() === 0 && $this->record->getMedia('images')->count() > 0) {
                $firstMedia = $this->record->getMedia('images')->first();
                $firstMedia->setCustomProperty('is_cover', true);
                $firstMedia->save();
            }
        }
    }
}