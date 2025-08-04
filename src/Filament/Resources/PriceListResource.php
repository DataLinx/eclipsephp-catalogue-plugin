<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\PriceListResource\Pages;
use Eclipse\Catalogue\Models\PriceList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriceListResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PriceList::class;

    protected static ?string $slug = 'price-lists';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('eclipse-catalogue::price-list.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('eclipse-catalogue::price-list.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('eclipse-catalogue::price-list.fields.name'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label(__('eclipse-catalogue::price-list.fields.code'))
                    ->maxLength(255)
                    ->unique(
                        table: 'pim_price_lists',
                        column: 'code',
                        ignoreRecord: true
                    ),

                Select::make('currency_id')
                    ->label(__('eclipse-catalogue::price-list.fields.currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Toggle::make('tax_included')
                    ->label(__('eclipse-catalogue::price-list.fields.tax_included'))
                    ->default(false),

                Textarea::make('notes')
                    ->label(__('eclipse-catalogue::price-list.fields.notes'))
                    ->rows(3)
                    ->maxLength(65535),

                // Note: is_active, is_default, and is_default_purchase are handled
                // through the PriceListData relationship in the page classes

                Placeholder::make('created_at')
                    ->label(__('eclipse-catalogue::price-list.table.columns.created_at'))
                    ->content(fn (?PriceList $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label(__('eclipse-catalogue::price-list.table.columns.updated_at'))
                    ->content(fn (?PriceList $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currency.name')
                    ->label(__('eclipse-catalogue::price-list.table.columns.currency'))
                    ->sortable(),

                IconColumn::make('tax_included')
                    ->label(__('eclipse-catalogue::price-list.table.columns.tax_included'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('eclipse-catalogue::price-list.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('eclipse-catalogue::price-list.table.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }
}
