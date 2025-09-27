<?php

namespace Eclipse\Catalogue\Filament\Resources;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductStatus;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lightweight Product Resource for selector popups.
 *
 * This resource is designed to be used in modal/popup contexts
 * where we need a streamlined product selection interface.
 */
class ProductSelectorResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('public')
                    ->size(40)
                    ->defaultImageUrl('/images/placeholder-product.png'),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state)) {
                            $state = $state[app()->getLocale()] ?? reset($state);
                        }

                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('type.name')
                    ->label('Type')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'danger',
                        'Draft' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('product_type_id')
                    ->label('Type')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(function () {
                        return Category::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(function () {
                        return ProductStatus::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    }),

                SelectFilter::make('group')
                    ->label('Group')
                    ->relationship('groups', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['type', 'category', 'status', 'media'])
            ->whereNotNull('name');
    }

    /**
     * Get query excluding specific product IDs.
     */
    public static function getEloquentQueryExcluding(array $excludeIds = []): Builder
    {
        $query = static::getEloquentQuery();

        if (! empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query;
    }
}
