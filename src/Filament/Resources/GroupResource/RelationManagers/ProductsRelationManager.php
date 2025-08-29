<?php

namespace Eclipse\Catalogue\Filament\Resources\GroupResource\RelationManagers;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->copyable()
                    ->toggleable(),
            ])
            ->defaultSort('pim_group_has_product.sort')
            ->reorderable('pim_group_has_product.sort')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering')
                    ->icon($isReordering ? 'heroicon-o-x-mark' : 'heroicon-o-arrows-up-down')
                    ->color($isReordering ? 'danger' : 'primary')
            )
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('add_product')
                    ->label('Add product')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add Product to Group')
                    ->modalSubmitActionLabel('Add Product')
                    ->modalCancelActionLabel('Cancel')
                    ->form([
                        \Filament\Forms\Components\Select::make('product_id')
                            ->label('Select product')
                            ->options(function () {
                                $group = $this->getOwnerRecord();
                                $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
                                $currentTenant = \Filament\Facades\Filament::getTenant();

                                $query = \Eclipse\Catalogue\Models\Product::query();

                                if ($currentTenant) {
                                    $query->whereHas('productData', function ($q) use ($tenantFK, $currentTenant) {
                                        $q->where($tenantFK, $currentTenant->id);
                                    });
                                }

                                // Exclude products already in this group
                                $existingProductIds = $group->products()->pluck('catalogue_products.id')->toArray();
                                $query->whereNotIn('catalogue_products.id', $existingProductIds);

                                return $query->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $group = $this->getOwnerRecord();
                        $product = \Eclipse\Catalogue\Models\Product::find($data['product_id']);

                        if ($product && ! $group->hasProduct($product)) {
                            $group->addProduct($product);

                            \Filament\Notifications\Notification::make()
                                ->title('Product added to group')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_product')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record): string => ProductResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('move_product')
                    ->label('Reorder')
                    ->icon('heroicon-o-arrows-up-down')
                    ->modalHeading(fn ($record) => 'Reorder: '.$record->name)
                    ->modalSubmitActionLabel('Move Product')
                    ->modalCancelActionLabel('Cancel')
                    ->form(function ($record, $livewire) {
                        return [
                            Forms\Components\Placeholder::make('moving_info')
                                ->label('You are moving')
                                ->content($record->name),

                            Forms\Components\Select::make('reference_id')
                                ->label('Place relative to')
                                ->options(
                                    $livewire->getOwnerRecord()
                                        ->products()
                                        ->pluck('name', 'id')
                                        ->except($record->id)
                                )
                                ->searchable()
                                ->reactive()
                                ->required(),

                            Forms\Components\Radio::make('position')
                                ->label('Position')
                                ->options([
                                    'before' => 'Before selected product',
                                    'after' => 'After selected product',
                                ])
                                ->default('before')
                                ->inline()
                                ->reactive(),

                            Forms\Components\Placeholder::make('preview')
                                ->label('Result')
                                ->content(function (callable $get, $livewire) use ($record) {
                                    $refId = $get('reference_id');
                                    $position = $get('position') ?? 'before';

                                    if (! $refId) {
                                        return 'Select a product to see the result.';
                                    }

                                    $refName = $livewire->getOwnerRecord()
                                        ->products()
                                        ->where('catalogue_products.id', $refId)
                                        ->value('name');

                                    if (! $refName) {
                                        return 'Select a valid product.';
                                    }

                                    return sprintf(
                                        '%s will be moved %s %s',
                                        $record->name,
                                        $position,
                                        $refName
                                    );
                                }),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $group = $this->getOwnerRecord();

                        $reference = $group->products()
                            ->where('catalogue_products.id', $data['reference_id'])
                            ->firstOrFail();

                        if ($data['position'] === 'before') {
                            $previous = $group->products()
                                ->wherePivot('sort', '<', $reference->pivot->sort)
                                ->orderByDesc('pim_group_has_product.sort')
                                ->first();

                            $newSort = $previous
                                ? intdiv($previous->pivot->sort + $reference->pivot->sort, 2)
                                : $reference->pivot->sort - 1000;
                        } else {
                            $next = $group->products()
                                ->wherePivot('sort', '>', $reference->pivot->sort)
                                ->orderBy('pim_group_has_product.sort')
                                ->first();

                            $newSort = $next
                                ? intdiv($reference->pivot->sort + $next->pivot->sort, 2)
                                : $reference->pivot->sort + 1000;
                        }

                        $group->updateProductSort($record, $newSort);
                    }),

                Tables\Actions\Action::make('remove_product')
                    ->label('Remove')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove Product from Group')
                    ->modalDescription('Are you sure you want to remove this product from the group?')
                    ->modalSubmitActionLabel('Remove Product')
                    ->action(function ($record) {
                        $group = $this->getOwnerRecord();
                        $group->removeProduct($record);

                        \Filament\Notifications\Notification::make()
                            ->title('Product removed from group')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('remove_products')
                        ->label('Remove from Group')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Remove Products from Group')
                        ->modalDescription('Are you sure you want to remove the selected products from this group?')
                        ->modalSubmitActionLabel('Remove Products')
                        ->action(function ($records) {
                            $group = $this->getOwnerRecord();
                            $removedCount = 0;

                            foreach ($records as $record) {
                                if ($group->hasProduct($record)) {
                                    $group->removeProduct($record);
                                    $removedCount++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Removed {$removedCount} products from group")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
