<?php

namespace Eclipse\Catalogue\Filament\Resources;

use Eclipse\Catalogue\Enums\BackgroundType;
use Eclipse\Catalogue\Enums\GradientDirection;
use Eclipse\Catalogue\Enums\GradientStyle;
use Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;
use Eclipse\Catalogue\Models\PropertyValue;
use Eclipse\Catalogue\Values\Background;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PropertyValueResource extends Resource
{
    use Translatable;

    protected static ?string $model = PropertyValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\Hidden::make('property_id')
                ->default(fn () => request()->has('property') ? (int) request('property') : null),

            Forms\Components\TextInput::make('value')
                ->label(__('eclipse-catalogue::property-value.fields.value'))
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('info_url')
                ->label(__('eclipse-catalogue::property-value.fields.info_url'))
                ->helperText(__('eclipse-catalogue::property-value.help_text.info_url'))
                ->url()
                ->maxLength(255),

            Forms\Components\FileUpload::make('image')
                ->label(__('eclipse-catalogue::property-value.fields.image'))
                ->helperText(__('eclipse-catalogue::property-value.help_text.image'))
                ->image()
                ->formatStateUsing(function ($state) {
                    if (is_string($state) || $state === null) {
                        return $state;
                    }

                    if (is_array($state)) {
                        $locale = app()->getLocale();
                        $byLocale = $state[$locale] ?? null;
                        if (is_string($byLocale) && $byLocale !== '') {
                            return $byLocale;
                        }

                        foreach ($state as $value) {
                            if (is_string($value) && $value !== '') {
                                return $value;
                            }
                        }

                        return null;
                    }

                    return null;
                })
                ->nullable()
                ->disk('public')
                ->directory('property-values'),
        ];

        if (request()->has('property')) {
            $prop = \Eclipse\Catalogue\Models\Property::find((int) request('property'));
            if ($prop && $prop->isColorType()) {
                $colorGroup = static::buildColorGroupSchema();
                array_splice($schema, 1, 0, $colorGroup);
            }
        }

        return $form->schema($schema)->columns(1);
    }

    public static function table(Table $table): Table
    {
        $table = $table
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label(__('eclipse-catalogue::property-value.table.columns.value'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ViewColumn::make('group_and_aliases')
                    ->label(__('eclipse-catalogue::property-value.table.columns.group'))
                    ->view('eclipse-catalogue::filament.columns.group-and-aliases')
                    ->extraAttributes(['class' => 'space-x-1']),

                Tables\Columns\TextColumn::make('color_swatch')
                    ->label('Color')
                    ->state(fn ($record) => $record->getColor())
                    ->formatStateUsing(function ($state) {
                        $base = 'display:inline-block;width:20px;height:20px;border-radius:4px;outline:1px solid rgba(0,0,0,.15);';
                        $bg = is_string($state) ? $state : '';

                        return '<span style="'.$base.$bg.'"></span>';
                    })
                    ->html(),

                Tables\Columns\ImageColumn::make('image')
                    ->label(__('eclipse-catalogue::property-value.table.columns.image'))
                    ->disk('public')
                    ->size(40),

                Tables\Columns\TextColumn::make('info_url')
                    ->label(__('eclipse-catalogue::property-value.table.columns.info_url'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('eclipse-catalogue::property-value.table.columns.products_count'))
                    ->counts('products'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('eclipse-catalogue::property-value.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('property')
                    ->label(__('eclipse-catalogue::property-value.table.filters.property'))
                    ->relationship('property', 'name')
                    ->default(fn () => request('property')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->modalWidth('lg')
                        ->modalHeading(__('eclipse-catalogue::property-value.modal.edit_heading'))
                        ->form(function (Form $form) {
                            $schema = [
                                Forms\Components\TextInput::make('value')
                                    ->label(__('eclipse-catalogue::property-value.fields.value'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('info_url')
                                    ->label(__('eclipse-catalogue::property-value.fields.info_url'))
                                    ->helperText(__('eclipse-catalogue::property-value.help_text.info_url'))
                                    ->url()
                                    ->maxLength(255),

                                Forms\Components\FileUpload::make('image')
                                    ->label(__('eclipse-catalogue::property-value.fields.image'))
                                    ->helperText(__('eclipse-catalogue::property-value.help_text.image'))
                                    ->image()
                                    ->nullable()
                                    ->disk('public')
                                    ->directory('property-values'),
                            ];

                            $prop = null;
                            if (request()->has('property')) {
                                $prop = \Eclipse\Catalogue\Models\Property::find((int) request('property'));
                            } elseif ($record = $form->getModelInstance()) {
                                if (method_exists($record, 'property')) {
                                    $prop = $record->property;
                                }
                            }

                            if ($prop && $prop->isColorType()) {
                                $colorGroup = static::buildColorGroupSchema();
                                array_splice($schema, 1, 0, $colorGroup);
                            }

                            return $form->schema($schema)->columns(1);
                        }),
                    Tables\Actions\Action::make('merge')
                        ->label(__('eclipse-catalogue::property-value.table.actions.merge'))
                        ->icon('heroicon-o-arrow-uturn-right')
                        ->modalHeading(__('eclipse-catalogue::property-value.modal.merge_heading'))
                        ->form(function (PropertyValue $record) {
                            return [
                                \Filament\Forms\Components\Placeholder::make('current_value')
                                    ->label(__('eclipse-catalogue::property-value.modal.merge_from_label'))
                                    ->content($record->value),
                                \Filament\Forms\Components\Select::make('target_id')
                                    ->label(__('eclipse-catalogue::property-value.modal.merge_to_label'))
                                    ->required()
                                    ->options(
                                        PropertyValue::query()
                                            ->where('property_id', $record->property_id)
                                            ->whereKeyNot($record->id)
                                            ->orderBy('value')
                                            ->pluck('value', 'id')
                                    ),
                                \Filament\Forms\Components\Placeholder::make('merge_helper')
                                    ->label('')
                                    ->content(__('eclipse-catalogue::property-value.modal.merge_helper'))
                                    ->columnSpanFull(),
                            ];
                        })
                        ->modalSubmitActionLabel(__('eclipse-catalogue::property-value.modal.merge_submit_label'))
                        ->modalCancelActionLabel(__('eclipse-catalogue::property-value.modal.cancel_label'))
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-o-question-mark-circle')
                        ->modalHeading(__('eclipse-catalogue::property-value.modal.merge_confirm_title'))
                        ->modalDescription(__('eclipse-catalogue::property-value.modal.merge_confirm_body'))
                        ->action(function (PropertyValue $record, array $data) {
                            try {
                                $result = $record->mergeInto((int) $data['target_id']);

                                Notification::make()
                                    ->title(__('eclipse-catalogue::property-value.messages.merged_title'))
                                    ->body(__('eclipse-catalogue::property-value.messages.merged_body', ['affected' => $result['affected_products']]))
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                \Log::error('Merge property values failed', ['exception' => $e]);
                                Notification::make()
                                    ->title(__('eclipse-catalogue::property-value.messages.merged_error_title'))
                                    ->body(__('eclipse-catalogue::property-value.messages.merged_error_body'))
                                    ->danger()
                                    ->send();
                                throw $e;
                            }
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('group_values')
                        ->label(__('eclipse-catalogue::property-value.table.actions.group_aliases'))
                        ->icon('heroicon-o-rectangle-group')
                        ->form(function (\Filament\Tables\Actions\BulkAction $action) {
                            $firstRecord = $action->getRecords()->first();
                            $derivedPropertyId = $firstRecord?->property_id ?? (request()->has('property') ? (int) request('property') : null);

                            return [
                                \Filament\Forms\Components\View::make('eclipse-catalogue::filament.bulk.group-selected-preview')
                                    ->statePath('selected_records')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component) use ($action) {
                                        $component->state($action->getRecords());
                                    })
                                    ->columnSpanFull(),

                                \Filament\Forms\Components\Hidden::make('property_id')
                                    ->default($derivedPropertyId),

                                \Filament\Forms\Components\Select::make('target_id')
                                    ->label(__('eclipse-catalogue::property-value.modal_grouping.target_label'))
                                    ->helperText(__('eclipse-catalogue::property-value.grouping.helper_target'))
                                    ->required()
                                    ->options(function (\Filament\Forms\Get $get) {
                                        $query = PropertyValue::query();
                                        $propertyId = $get('property_id');
                                        if ($propertyId) {
                                            $query->sameProperty((int) $propertyId);
                                        }

                                        return $query->orderBy('value')->pluck('value', 'id');
                                    })
                                    ->searchable(),
                            ];
                        })
                        ->action(function (\Illuminate\Support\Collection $records, array $data) {
                            try {
                                if ($records->isEmpty()) {
                                    return;
                                }

                                $target = PropertyValue::findOrFail((int) $data['target_id']);

                                $sourceIds = $records->pluck('id');
                                if ($sourceIds->contains($target->id)) {
                                    Notification::make()->title(__('eclipse-catalogue::property-value.grouping.error_title'))->body(__('eclipse-catalogue::property-value.grouping.errors.target_in_sources'))->danger()->send();

                                    return;
                                }

                                if ($target->group_value_id !== null) {
                                    Notification::make()->title(__('eclipse-catalogue::property-value.grouping.error_title'))->body(__('eclipse-catalogue::property-value.grouping.errors.target_is_member'))->danger()->send();

                                    return;
                                }

                                $updated = 0;
                                foreach ($records as $record) {
                                    /** @var PropertyValue $source */
                                    $source = $record instanceof PropertyValue ? $record : PropertyValue::findOrFail($record);
                                    if ($source->property_id !== $target->property_id) {
                                        Notification::make()->title(__('eclipse-catalogue::property-value.grouping.error_title'))->body(__('eclipse-catalogue::property-value.grouping.errors.different_property'))->danger()->send();

                                        return;
                                    }
                                    $source->groupInto($target->id);
                                    $updated++;
                                }

                                Notification::make()->title(__('eclipse-catalogue::property-value.grouping.success_grouped_title'))
                                    ->body(__('eclipse-catalogue::property-value.grouping.success_grouped_body', ['count' => $updated, 'target' => $target->value]))
                                    ->success()->send();
                            } catch (\Throwable $e) {
                                \Log::error('Bulk group failed', ['exception' => $e]);
                                Notification::make()->title(__('eclipse-catalogue::property-value.grouping.error_title'))->body(__('eclipse-catalogue::property-value.messages.merged_error_body'))->danger()->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('ungroup_values')
                        ->label(__('eclipse-catalogue::property-value.table.actions.remove_from_group'))
                        ->icon('heroicon-o-squares-2x2')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            try {
                                $updated = 0;
                                foreach ($records as $record) {
                                    /** @var PropertyValue $model */
                                    $model = $record instanceof PropertyValue ? $record : PropertyValue::findOrFail($record);
                                    $model->removeFromGroup();
                                    $updated++;
                                }
                                Notification::make()->title(__('eclipse-catalogue::property-value.grouping.success_ungrouped_title'))
                                    ->body(__('eclipse-catalogue::property-value.grouping.success_ungrouped_body', ['count' => $updated]))
                                    ->success()->send();
                            } catch (\Throwable $e) {
                                \Log::error('Bulk ungroup failed', ['exception' => $e]);
                                Notification::make()->title(__('eclipse-catalogue::property-value.grouping.error_title'))->body(__('eclipse-catalogue::property-value.messages.merged_error_body'))->danger()->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        return $table;
    }

    public static function buildColorGroupSchema(): array
    {
        return [
            Forms\Components\Group::make()
                ->statePath('color')
                ->dehydrated(true)
                ->afterStateHydrated(function (\Filament\Forms\Components\Group $component, $state) {
                    if (is_string($state)) {
                        $decoded = json_decode($state, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $component->state($decoded);
                        }
                    }
                })
                ->dehydrateStateUsing(fn ($state) => $state)
                ->schema([
                    Forms\Components\Radio::make('type')
                        ->options(fn () => collect(BackgroundType::cases())
                            ->mapWithKeys(fn (BackgroundType $e) => [$e->value => $e->getLabel()])
                            ->toArray())
                        ->default(BackgroundType::NONE->value)
                        ->live(),
                    Forms\Components\ColorPicker::make('color')
                        ->visible(fn (Forms\Get $get) => $get('type') === 's')
                        ->live(),
                    Forms\Components\Grid::make()
                        ->columns(4)
                        ->visible(fn (Forms\Get $get) => $get('type') === 'g')
                        ->schema([
                            Forms\Components\ColorPicker::make('color_start')->columnSpan(2)->live(),
                            Forms\Components\ColorPicker::make('color_end')->columnSpan(2)->live(),
                            Forms\Components\Select::make('gradient_direction')
                                ->options(fn () => collect(GradientDirection::cases())
                                    ->mapWithKeys(fn (GradientDirection $e) => [$e->value => $e->getLabel()])
                                    ->toArray())
                                ->default(GradientDirection::BOTTOM->value)->columnSpan(2)->live(),
                            Forms\Components\Radio::make('gradient_style')
                                ->options(fn () => collect(GradientStyle::cases())
                                    ->mapWithKeys(fn (GradientStyle $e) => [$e->value => $e->getLabel()])
                                    ->toArray())
                                ->inline()
                                ->inlineLabel(false)
                                ->default(GradientStyle::SHARP->value)
                                ->columnSpan(2)
                                ->live(),
                        ]),
                    Forms\Components\ViewField::make('preview')
                        ->view('eclipse-catalogue::components.color-preview')
                        ->visible(function (Forms\Get $get) {
                            $bg = Background::fromForm([
                                'type' => $get('type'),
                                'color' => $get('color'),
                                'color_start' => $get('color_start'),
                                'color_end' => $get('color_end'),
                                'gradient_direction' => $get('gradient_direction'),
                                'gradient_style' => $get('gradient_style'),
                            ]);

                            return $bg->hasRenderableCss();
                        })
                        ->viewData(function (Forms\Get $get) {
                            $bg = Background::fromForm([
                                'type' => $get('type'),
                                'color' => $get('color'),
                                'color_start' => $get('color_start'),
                                'color_end' => $get('color_end'),
                                'gradient_direction' => $get('gradient_direction'),
                                'gradient_style' => $get('gradient_style'),
                            ]);

                            return ['style' => $bg->toCss(), 'isMulti' => $bg->isMulticolor()];
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPropertyValues::route('/'),
        ];
    }

    /**
     * Attributes stored as JSON translations on the model.
     */
    public static function getTranslatableAttributes(): array
    {
        return [
            'value',
            'info_url',
            'image',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (request()->has('property')) {
            $query->where('property_id', request('property'));
        }

        return $query->with(['group', 'members'])->groupedOrder();
    }
}
