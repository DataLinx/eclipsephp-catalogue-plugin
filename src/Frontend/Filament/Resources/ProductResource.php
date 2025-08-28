<?php

namespace Eclipse\Catalogue\Frontend\Filament\Resources;

use Eclipse\Catalogue\Frontend\Filament\Resources\ProductResource\Pages;
use Eclipse\Catalogue\Models\Product;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'products';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code'),

                TextInput::make('barcode'),

                TextInput::make('manufacturers_code'),

                TextInput::make('suppliers_code'),

                TextInput::make('net_weight')
                    ->numeric(),

                TextInput::make('gross_weight')
                    ->numeric(),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Product $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Product $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                TextInput::make('category_id')
                    ->integer(),

                Checkbox::make('registerMediaConversionsUsingModelInstance'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code'),

                TextColumn::make('barcode'),

                TextColumn::make('manufacturers_code'),

                TextColumn::make('suppliers_code'),

                TextColumn::make('net_weight'),

                TextColumn::make('gross_weight'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('short_description'),

                TextColumn::make('description'),

                TextColumn::make('category_id'),

                TextColumn::make('registerMediaConversionsUsingModelInstance'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
