<?php

namespace Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;

use Eclipse\Catalogue\Enums\PropertyType;
use Eclipse\Catalogue\Filament\Resources\PropertyResource;
use Eclipse\Catalogue\Filament\Resources\PropertyValueResource;
use Eclipse\Catalogue\Models\Property;
use Filament\Actions;
use Filament\Actions\LocaleSwitcher;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Filament\Tables;
use Filament\Tables\Table;

class ListPropertyValues extends ListRecords
{
    use Translatable;

    protected static string $resource = PropertyValueResource::class;

    public ?Property $property = null;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('property')) {
            $this->property = Property::find(request('property'));

            if ($this->property && $this->property->type === PropertyType::CUSTOM->value) {
                abort(404);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\CreateAction::make()
                ->modalWidth('lg')
                ->modalHeading(__('eclipse-catalogue::property-value.modal.create_heading'))
                ->form(function (\Filament\Forms\Form $form) {
                    $schema = [
                        \Filament\Forms\Components\TextInput::make('value')
                            ->label(__('eclipse-catalogue::property-value.fields.value'))
                            ->required()
                            ->maxLength(255),

                        \Filament\Forms\Components\TextInput::make('info_url')
                            ->label(__('eclipse-catalogue::property-value.fields.info_url'))
                            ->helperText(__('eclipse-catalogue::property-value.help_text.info_url'))
                            ->url()
                            ->maxLength(255),

                        \Filament\Forms\Components\FileUpload::make('image')
                            ->label(__('eclipse-catalogue::property-value.fields.image'))
                            ->helperText(__('eclipse-catalogue::property-value.help_text.image'))
                            ->image()
                            ->nullable()
                            ->disk('public')
                            ->directory('property-values'),
                    ];

                    if ($this->property && $this->property->type === PropertyType::COLOR->value) {
                        $schema = array_merge($schema, PropertyValueResource::buildColorGroupSchema());
                    }

                    return $form->schema($schema)->columns(1);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    // Set the property_id from the request if available
                    if (request()->has('property')) {
                        $data['property_id'] = (int) request('property');
                    }

                    return $data;
                }),
        ];
    }

    public function getTitle(): string
    {
        return $this->property
            ? __('eclipse-catalogue::property-value.pages.title.with_property', ['property' => $this->property->name])
            : __('eclipse-catalogue::property-value.pages.title.default');
    }

    public function getBreadcrumbs(): array
    {
        if ($this->property) {
            return [
                PropertyResource::getUrl('index') => __('eclipse-catalogue::property-value.pages.breadcrumbs.properties'),
                PropertyResource::getUrl('edit', ['record' => $this->property]) => $this->property->name,
                request()->url() => __('eclipse-catalogue::property-value.pages.breadcrumbs.list'),
            ];
        }

        return [
            PropertyValueResource::getUrl('index') => __('eclipse-catalogue::property-value.pages.title.default'),
            request()->url() => __('eclipse-catalogue::property-value.pages.breadcrumbs.list'),
        ];
    }

    public function getTable(): Table
    {
        return parent::getTable()
            ->reorderable('sort', $this->property?->enable_sorting)
            ->defaultSort($this->property?->enable_sorting ? 'sort' : 'value')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering')
                    ->icon($isReordering ? 'heroicon-o-x-mark' : 'heroicon-o-arrows-up-down')
                    ->color($isReordering ? 'danger' : 'primary')
                    ->extraAttributes(['class' => 'reorder-trigger'])
            );
    }
}
