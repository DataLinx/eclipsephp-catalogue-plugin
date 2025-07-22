<?php

namespace Eclipse\Catalogue\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageManager extends Field
{
    protected string $view = 'eclipse-catalogue::filament.forms.components.image-manager';

    protected string $collection = 'images';

    protected array $acceptedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
        $this->dehydrated(false);

        $this->afterStateHydrated(function (Field $component) {
            if ($component->getRecord()) {
                $component->refreshState();
            }
        });

        $this->afterStateUpdated(function (Field $component) {
            if ($component->getRecord()) {
                $component->refreshState();
            }
        });

        $this->registerActions([
            $this->getUploadAction(),
            $this->getUrlUploadAction(),
            $this->getEditAction(),
            $this->getDeleteAction(),
            $this->getCoverAction(),
        ]);

        $this->dehydrateStateUsing(function ($state, ?Model $record) {
            if (! $record || ! $state) {
                return null;
            }

            $existingIds = collect($state)->pluck('id')->filter()->toArray();

            $record->getMedia($this->collection)
                ->whereNotIn('id', $existingIds)
                ->each(fn ($media) => $media->delete());

            collect($state)->each(function ($item, $index) use ($record) {
                if (! isset($item['id'])) {
                    return;
                }

                $media = $record->getMedia($this->collection)->firstWhere('id', $item['id']);
                if (! $media) {
                    return;
                }

                $media->setCustomProperty('name', $item['name'] ?? []);
                $media->setCustomProperty('description', $item['description'] ?? []);
                $media->setCustomProperty('is_cover', $item['is_cover'] ?? false);
                $media->setCustomProperty('position', $index);
                $media->save();
            });

            $this->ensureSingleCoverImage($record);

            return null;
        });
    }

    public function collection(string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    public function getAvailableLocales(): array
    {
        $locales = [];

        try {
            $livewire = $this->getLivewire();

            if ($livewire && method_exists($livewire, 'getTranslatableLocales')) {
                $plugin = filament('spatie-laravel-translatable');
                foreach ($livewire->getTranslatableLocales() as $locale) {
                    $locales[$locale] = $plugin->getLocaleLabel($locale) ?? $locale;
                }
            }
        } catch (\Exception $e) {
        }

        if (empty($locales)) {
            $locales = config('eclipsephp.locales', ['en' => 'English']);
        }

        return $locales;
    }

    public function getSelectedLocale(): string
    {
        try {
            $livewire = $this->getLivewire();
            if ($livewire && property_exists($livewire, 'activeLocale')) {
                return $livewire->activeLocale;
            }
        } catch (\Exception $e) {
        }

        return app()->getLocale();
    }

    public function getUploadAction(): Action
    {
        return Action::make('upload')
            ->label('Upload Files')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->modalHeading('Upload Images')
            ->modalSubmitActionLabel('Upload')
            ->modalIcon('heroicon-o-photo')
            ->form([
                FileUpload::make('files')
                    ->label('Choose files')
                    ->multiple()
                    ->image()
                    ->acceptedFileTypes($this->acceptedFileTypes)
                    ->imagePreviewHeight('200')
                    ->required()
                    ->storeFiles(false),
            ])
            ->action(function (array $data, ?Model $record): void {
                if (! $record || ! isset($data['files'])) {
                    return;
                }

                $existingCount = $record->getMedia($this->collection)->count();
                $maxPosition = $record->getMedia($this->collection)->max(fn ($m) => $m->getCustomProperty('position', 0)) ?? -1;
                $uploadCount = 0;

                foreach ($data['files'] as $file) {
                    if ($file instanceof TemporaryUploadedFile) {
                        $record->addMedia($file)
                            ->usingFileName($file->getClientOriginalName())
                            ->withCustomProperties([
                                'name' => [],
                                'description' => [],
                                'is_cover' => $existingCount === 0 && $uploadCount === 0,
                                'position' => ++$maxPosition,
                            ])
                            ->toMediaCollection($this->collection);

                        $uploadCount++;
                    }
                }

                $this->refreshState();

                Notification::make()
                    ->title("{$uploadCount} image(s) uploaded successfully")
                    ->success()
                    ->send();
            })
            ->modalWidth('lg')
            ->closeModalByClickingAway(false);
    }

    public function getUrlUploadAction(): Action
    {
        return Action::make('urlUpload')
            ->label('Add from URL')
            ->icon('heroicon-o-link')
            ->color('gray')
            ->modalHeading('Add Images from URLs')
            ->modalSubmitActionLabel('Add Images')
            ->modalIcon('heroicon-o-link')
            ->form([
                Textarea::make('urls')
                    ->label('Image URLs')
                    ->placeholder("https://example.com/image1.jpg\nhttps://example.com/image2.jpg")
                    ->rows(5)
                    ->required()
                    ->helperText('Enter one URL per line'),
            ])
            ->action(function (array $data, ?Model $record): void {
                if (! $record || ! isset($data['urls'])) {
                    return;
                }

                $existingCount = $record->getMedia($this->collection)->count();
                $maxPosition = $record->getMedia($this->collection)->max(fn ($m) => $m->getCustomProperty('position', 0)) ?? -1;

                $urls = array_filter(array_map('trim', explode("\n", $data['urls'])));
                $successCount = 0;
                $failedUrls = [];

                foreach ($urls as $url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        try {
                            $record->addMediaFromUrl($url)
                                ->withCustomProperties([
                                    'name' => [],
                                    'description' => [],
                                    'is_cover' => $existingCount === 0 && $successCount === 0,
                                    'position' => ++$maxPosition,
                                ])
                                ->toMediaCollection($this->collection);

                            $successCount++;
                        } catch (\Exception $e) {
                            $failedUrls[] = $url;
                        }
                    } else {
                        $failedUrls[] = $url;
                    }
                }

                $this->refreshState();

                if ($successCount > 0) {
                    Notification::make()
                        ->title("{$successCount} image(s) added successfully")
                        ->success()
                        ->send();
                }

                if (! empty($failedUrls)) {
                    Notification::make()
                        ->title('Some URLs failed')
                        ->body("Failed URLs: " . implode(', ', array_slice($failedUrls, 0, 3)) . (count($failedUrls) > 3 ? " and " . (count($failedUrls) - 3) . " more" : ""))
                        ->warning()
                        ->send();
                }
            })
            ->modalWidth('lg')
            ->closeModalByClickingAway(false);
    }

    public function getEditAction(): Action
    {
        return Action::make('editImage')
            ->label('Edit Image')
            ->modalHeading('Edit Image Details')
            ->modalSubmitActionLabel('Save Changes')
            ->form(function (array $arguments) {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;
                $selectedLocale = $args['selectedLocale'] ?? $this->getSelectedLocale();
                $state = $this->getState();
                $image = collect($state)->firstWhere('uuid', $uuid);

                if (! $image) {
                    return [];
                }

                $locales = $this->getAvailableLocales();

                $fields = [];

                $fields[] = Placeholder::make('preview')
                    ->label('')
                    ->content(function () use ($image) {
                        return view('eclipse-catalogue::filament.forms.components.image-preview-inline', [
                            'url' => $image['preview_url'] ?? $image['url'],
                            'filename' => $image['file_name'],
                        ]);
                    });

                if (count($locales) > 1) {
                    $fields[] = Select::make('edit_locale')
                        ->label('Language')
                        ->options($locales)
                        ->default($selectedLocale)
                        ->live()
                        ->afterStateUpdated(function ($state, $set) use ($image) {
                            $set('name', $image['name'][$state] ?? '');
                            $set('description', $image['description'][$state] ?? '');
                        });
                }

                $fields[] = TextInput::make('name')
                    ->label('Name')
                    ->default($image['name'][$selectedLocale] ?? '');

                $fields[] = Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->default($image['description'][$selectedLocale] ?? '');

                return $fields;
            })
            ->action(function (array $data, array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;
                $record = $this->getRecord();

                if (! $uuid || ! $record) {
                    return;
                }

                $media = $record->getMedia($this->collection)->firstWhere('uuid', $uuid);
                if ($media) {
                    $nameTranslations = $media->getCustomProperty('name', []);
                    $descriptionTranslations = $media->getCustomProperty('description', []);

                    $locale = $data['edit_locale'] ?? array_key_first($this->getAvailableLocales());
                    $nameTranslations[$locale] = $data['name'] ?? '';
                    $descriptionTranslations[$locale] = $data['description'] ?? '';

                    $media->setCustomProperty('name', $nameTranslations);
                    $media->setCustomProperty('description', $descriptionTranslations);
                    $media->save();

                    $this->refreshState();

                    Notification::make()
                        ->title('Image details updated')
                        ->success()
                        ->send();
                }
            })
            ->modalWidth('lg');
    }

    public function getCoverAction(): Action
    {
        return Action::make('setCover')
            ->label('Set as Cover')
            ->requiresConfirmation()
            ->modalHeading('Set as Cover Image')
            ->modalDescription('This image will be used as the main product image.')
            ->modalSubmitActionLabel('Set as Cover')
            ->action(function (array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;
                $record = $this->getRecord();

                if (! $uuid || ! $record) {
                    return;
                }

                $record->getMedia($this->collection)->each(function ($media) {
                    $media->setCustomProperty('is_cover', false);
                    $media->save();
                });

                $targetMedia = $record->getMedia($this->collection)->firstWhere('uuid', $uuid);
                if ($targetMedia) {
                    $targetMedia->setCustomProperty('is_cover', true);
                    $targetMedia->save();
                }

                $this->refreshState();

                Notification::make()
                    ->title('Cover image updated')
                    ->success()
                    ->send();
            });
    }

    protected function mediaToArray(Media $media): array
    {
        return [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'url' => $media->getUrl(),
            'thumb_url' => $media->getUrl('thumb'),
            'preview_url' => $media->getUrl('preview'),
            'name' => $media->getCustomProperty('name', []),
            'description' => $media->getCustomProperty('description', []),
            'is_cover' => $media->getCustomProperty('is_cover', false),
            'position' => $media->getCustomProperty('position', 0),
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
        ];
    }

    public function refreshState(): void
    {
        $record = $this->getRecord();
        if (! $record) {
            $this->state([]);

            return;
        }

        $record->load('media');

        $media = $record->getMedia($this->collection)
            ->map(fn (Media $media) => $this->mediaToArray($media))
            ->sortBy('position')
            ->values()
            ->toArray();

        $this->state($media);
    }

    protected function ensureSingleCoverImage(Model $record): void
    {
        $coverMedia = $record->getMedia($this->collection)
            ->filter(fn ($media) => $media->getCustomProperty('is_cover', false));

        if ($coverMedia->count() > 1) {
            $coverMedia->skip(1)->each(function ($media) {
                $media->setCustomProperty('is_cover', false);
                $media->save();
            });
        }

        if ($coverMedia->count() === 0 && $record->getMedia($this->collection)->count() > 0) {
            $firstMedia = $record->getMedia($this->collection)->first();
            $firstMedia->setCustomProperty('is_cover', true);
            $firstMedia->save();
        }
    }

    public function getDeleteAction(): Action
    {
        return Action::make('deleteImage')
            ->label('Delete')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete Image')
            ->modalDescription('Are you sure you want to delete this image? This action cannot be undone.')
            ->modalSubmitActionLabel('Delete')
            ->action(function (array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;
                $record = $this->getRecord();

                if (! $uuid || ! $record) {
                    return;
                }

                $media = $record->getMedia($this->collection)->firstWhere('uuid', $uuid);

                if (! $media) {
                    Notification::make()
                        ->title('Could not find image to delete')
                        ->warning()
                        ->send();

                    return;
                }

                $wasCover = $media->getCustomProperty('is_cover', false);

                $media->delete();

                $record->load('media');

                if ($wasCover) {
                    $remainingMedia = $record->getMedia($this->collection);
                    if ($remainingMedia->count() > 0) {
                        $firstMedia = $remainingMedia->first();
                        $firstMedia->setCustomProperty('is_cover', true);
                        $firstMedia->save();
                    }
                }

                $this->refreshState();

                Notification::make()
                    ->title('Image deleted')
                    ->success()
                    ->send();
            });
    }
}
