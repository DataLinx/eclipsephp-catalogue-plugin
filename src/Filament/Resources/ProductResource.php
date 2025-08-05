<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;
use Eclipse\Catalogue\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $slug = 'products';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Basic Information'))
                    ->compact()
                    ->schema([
                        TextInput::make('code'),
                        TextInput::make('barcode')->maxLength(255),
                        TextInput::make('name')
                            ->columnSpanFull(),
                        Select::make('origin_country_id')
                            ->label('Country of Origin')
                            ->relationship('originCountry', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('short_description'),
                        RichEditor::make('description')->columnSpanFull(),
                        Placeholder::make('created_at')
                            ->label('Created Date')
                            ->content(fn (?Product $record): string => $record?->created_at?->diffForHumans() ?? '-'),
                        Placeholder::make('updated_at')
                            ->label('Last Modified Date')
                            ->content(fn (?Product $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                    ])->columns(2),

                Section::make(__('Codes & Physical Properties'))
                    ->compact()
                    ->schema([
                        TextInput::make('manufacturers_code')
                            ->label('Manufacturer\'s Code')
                            ->maxLength(255),
                        TextInput::make('suppliers_code')
                            ->label('Supplier\'s Code')
                            ->maxLength(255),
                        TextInput::make('net_weight')->numeric(),
                        TextInput::make('gross_weight')->numeric(),
                    ])->columns(2),

                Section::make(__('Inventory Management'))
                    ->compact()
                    ->schema([
                        TextInput::make('stock_qty')
                            ->label('Stock Quantity')
                            ->numeric()
                            ->step(0.00001),
                        TextInput::make('min_stock_qty')
                            ->label('Minimum Stock Quantity')
                            ->numeric()
                            ->step(0.00001),
                        DatePicker::make('stock_date')->label('Date of Stock'),
                    ])->columns(3),

                Section::make(__('Product Settings'))
                    ->compact()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Product is Active')
                            ->default(false),
                        Toggle::make('free_delivery')
                            ->label('Enable Free Delivery')
                            ->default(false),
                        DatePicker::make('available_from_date')
                            ->label('Available From'),
                        TextInput::make('sort')->label('Sorting Label'),
                    ])->columns(2),

                Section::make(__('SEO & Meta Information'))
                    ->compact()
                    ->schema([
                        TextInput::make('meta_title')
                            ->columnSpanFull()
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('Recommended: 50-60 characters'),
                        Textarea::make('meta_desc')
                            ->label('Meta Description')
                            ->rows(3)
                            ->helperText('Recommended: 150-160 characters')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),

                TextColumn::make('name')
                    ->toggleable(false),

                TextColumn::make('short_description')
                    ->words(5),

                TextColumn::make('code')
                    ->copyable(),

                TextColumn::make('barcode'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->toggleable(),

                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->numeric(2)
                    ->color(fn ($record) => $record && $record->stock_qty <= $record->min_stock_qty ? 'danger' : 'success')
                    ->toggleable(),

                TextColumn::make('available_from_date')
                    ->label('Available From')
                    ->date()
                    ->placeholder('Always available')
                    ->toggleable(),

                IconColumn::make('free_delivery')
                    ->boolean()
                    ->label('Free Delivery')
                    ->toggleable(),

                TextColumn::make('originCountry.name')
                    ->label('Origin')
                    ->placeholder('Not specified')
                    ->toggleable(),

                TextColumn::make('manufacturers_code'),

                TextColumn::make('suppliers_code'),

                TextColumn::make('net_weight')
                    ->numeric(3)
                    ->suffix(' kg'),

                TextColumn::make('gross_weight')
                    ->numeric(3)
                    ->suffix(' kg'),
            ])
            ->searchable()
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'code',
            'barcode',
            'manufacturers_code',
            'suppliers_code',
            'name',
            'short_description',
            'description',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            'Code' => $record->code,
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
