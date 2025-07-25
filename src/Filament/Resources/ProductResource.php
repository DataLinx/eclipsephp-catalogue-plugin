<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Forms\Components\ImageManager;
use Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;
use Eclipse\Catalogue\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
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
                Tabs::make('Product Information')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->schema([
                                Section::make('Basic Information')
                                    ->compact()
                                    ->schema([
                                        TextInput::make('code')
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        TextInput::make('barcode')
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        TextInput::make('manufacturers_code')
                                            ->label('Manufacturer\'s Code')
                                            ->maxLength(255),

                                        TextInput::make('suppliers_code')
                                            ->label('Supplier\'s Code')
                                            ->maxLength(255),

                                        TextInput::make('net_weight')
                                            ->numeric()
                                            ->suffix('kg'),

                                        TextInput::make('gross_weight')
                                            ->numeric()
                                            ->suffix('kg'),
                                    ])
                                    ->columns(2),

                                Section::make('Product Details')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('short_description')
                                            ->maxLength(500),

                                        RichEditor::make('description')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Timestamps')
                                    ->schema([
                                        Placeholder::make('created_at')
                                            ->label('Created Date')
                                            ->content(fn (?Product $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                                        Placeholder::make('updated_at')
                                            ->label('Last Modified Date')
                                            ->content(fn (?Product $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                                    ])
                                    ->columns(2)
                                    ->hidden(fn (?Product $record) => $record === null),
                            ]),

                        Tabs\Tab::make('Images')
                            ->schema([
                                ImageManager::make('images')
                                    ->label('')
                                    ->collection('images')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
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
