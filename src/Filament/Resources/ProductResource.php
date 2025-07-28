<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;
use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextInput::make('code'),

                TextInput::make('barcode')
                    ->maxLength(255),

                TextInput::make('manufacturers_code')
                    ->label('Manufacturer\'s Code')
                    ->maxLength(255),

                TextInput::make('suppliers_code')
                    ->label('Supplier\'s Code')
                    ->maxLength(255),

                TextInput::make('net_weight')
                    ->numeric(),

                TextInput::make('gross_weight')
                    ->numeric(),

                TextInput::make('name'),

                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::getHierarchicalOptions())
                    ->searchable()
                    ->placeholder('Category (optional)'),

                TextInput::make('short_description'),

                RichEditor::make('description'),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn (?Product $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn (?Product $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),

                TextColumn::make('name')
                    ->toggleable(false),

                TextColumn::make('category.name'),

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
                SelectFilter::make('category_id')
                    ->label('Categories')
                    ->multiple()
                    ->options(Category::getHierarchicalOptions()),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
                    ->hiddenLabel()
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
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
