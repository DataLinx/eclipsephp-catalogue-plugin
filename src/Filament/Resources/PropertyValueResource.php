<?php

namespace Eclipse\Catalogue\Filament\Resources;

use Eclipse\Catalogue\Filament\Resources\PropertyValueResource\Pages;
use Eclipse\Catalogue\Models\PropertyValue;
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
        return $form
            ->schema([
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
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        $table = $table
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label(__('eclipse-catalogue::property-value.table.columns.value'))
                    ->searchable()
                    ->sortable(),

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
                        ->modalHeading(__('eclipse-catalogue::property-value.modal.edit_heading')),
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        return $table;
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

        return $query;
    }
}
