<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\Pages;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\RelationManagers;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('eclipse-catalogue::property.sections.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('eclipse-catalogue::property.fields.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label(__('eclipse-catalogue::property.fields.code'))
                            ->helperText(__('eclipse-catalogue::property.help_text.code'))
                            ->regex('/^[a-zA-Z0-9_]*$/')
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label(__('eclipse-catalogue::property.fields.description'))
                            ->rows(3),

                        Forms\Components\TextInput::make('internal_name')
                            ->label(__('eclipse-catalogue::property.fields.internal_name'))
                            ->helperText(__('eclipse-catalogue::property.help_text.internal_name'))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('eclipse-catalogue::property.sections.configuration'))
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('eclipse-catalogue::property.fields.is_active'))
                            ->default(true),

                        Forms\Components\Toggle::make('is_global')
                            ->label(__('eclipse-catalogue::property.fields.is_global'))
                            ->helperText(__('eclipse-catalogue::property.help_text.is_global'))
                            ->reactive(),

                        Forms\Components\Select::make('type')
                            ->label('Property Type')
                            ->options([
                                'list' => 'List (Predefined Values)',
                                'custom' => 'Custom (User Input)',
                            ])
                            ->default('list')
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('input_type')
                            ->label('Input Type')
                            ->options([
                                'string' => 'String (up to 255 chars)',
                                'text' => 'Text (up to 65k chars)',
                                'integer' => 'Integer',
                                'decimal' => 'Decimal',
                                'date' => 'Date',
                                'datetime' => 'Date & Time',
                                'file' => 'File',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('type') === 'custom')
                            ->required(fn (Forms\Get $get) => $get('type') === 'custom')
                            ->reactive(),

                        Forms\Components\Toggle::make('is_multilang')
                            ->label('Multilingual')
                            ->helperText('Enable translation support for string, text, and file inputs')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'custom' && in_array($get('input_type'), ['string', 'text', 'file']))
                            ->default(false),

                        Forms\Components\TextInput::make('max_values')
                            ->label(__('eclipse-catalogue::property.fields.max_values'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText(__('eclipse-catalogue::property.help_text.max_values'))
                            ->visible(fn (Forms\Get $get) => $get('type') === 'list' || $get('input_type') === 'file'),

                        Forms\Components\Toggle::make('enable_sorting')
                            ->label(__('eclipse-catalogue::property.fields.enable_sorting'))
                            ->helperText(__('eclipse-catalogue::property.help_text.enable_sorting')),

                        Forms\Components\Toggle::make('is_filter')
                            ->label(__('eclipse-catalogue::property.fields.is_filter'))
                            ->helperText(__('eclipse-catalogue::property.help_text.is_filter')),
                    ])->columns(2),

                Forms\Components\Section::make(__('eclipse-catalogue::property.sections.product_types'))
                    ->schema([
                        Forms\Components\CheckboxList::make('product_types')
                            ->label(__('eclipse-catalogue::property.fields.product_types'))
                            ->relationship('productTypes', 'name')
                            ->options(ProductType::pluck('name', 'id'))
                            ->helperText(__('eclipse-catalogue::property.help_text.product_types'))
                            ->hidden(fn (Forms\Get $get) => $get('is_global')),
                    ])
                    ->hidden(fn (Forms\Get $get) => $get('is_global')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('eclipse-catalogue::property.table.columns.code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('eclipse-catalogue::property.table.columns.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('internal_name')
                    ->label(__('eclipse-catalogue::property.table.columns.internal_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'list' => 'success',
                        'custom' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('input_type')
                    ->label('Input Type')
                    ->visible(fn (?Property $record) => $record && $record->isCustomType())
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_multilang')
                    ->label('Multilingual')
                    ->boolean()
                    ->visible(fn (?Property $record) => $record && $record->isCustomType() && $record->supportsMultilang()),

                Tables\Columns\IconColumn::make('is_global')
                    ->label(__('eclipse-catalogue::property.table.columns.is_global'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('max_values')
                    ->label(__('eclipse-catalogue::property.table.columns.max_values'))
                    ->numeric(),

                Tables\Columns\IconColumn::make('enable_sorting')
                    ->label(__('eclipse-catalogue::property.table.columns.enable_sorting'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_filter')
                    ->label(__('eclipse-catalogue::property.table.columns.is_filter'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('eclipse-catalogue::property.table.columns.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('values_count')
                    ->label(__('eclipse-catalogue::property.table.columns.values_count'))
                    ->counts('values'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('eclipse-catalogue::property.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label(__('eclipse-catalogue::property.table.filters.product_type'))
                    ->relationship('productTypes', 'name')
                    ->multiple(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Property Type')
                    ->options([
                        'list' => 'List',
                        'custom' => 'Custom',
                    ]),

                Tables\Filters\TernaryFilter::make('is_global')
                    ->label(__('eclipse-catalogue::property.table.filters.is_global')),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('eclipse-catalogue::property.table.filters.is_active')),

                Tables\Filters\TernaryFilter::make('is_filter')
                    ->label(__('eclipse-catalogue::property.table.filters.is_filter')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('values')
                        ->label(__('eclipse-catalogue::property.table.actions.values'))
                        ->icon('heroicon-o-list-bullet')
                        ->url(fn (Property $record): string => PropertyValueResource::getUrl('index', ['property' => $record->id])),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Property $record): string => PropertyValueResource::getUrl('index', ['property' => $record->id]))
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
        ];
    }
}
