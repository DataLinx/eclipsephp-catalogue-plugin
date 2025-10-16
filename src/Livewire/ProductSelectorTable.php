<?php

namespace Eclipse\Catalogue\Livewire;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductRelation;
use Eclipse\Catalogue\Models\ProductStatus;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Services\ProductRelationService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ProductSelectorTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public int $productId;

    public string $type;

    public array $persistentSelection = [];

    public function mount(int $productId, string $type): void
    {
        $this->productId = $productId;
        $this->type = $type;
    }

    public function toggleProductSelection(int $productId): void
    {
        if (in_array($productId, $this->persistentSelection)) {
            $this->persistentSelection = array_diff($this->persistentSelection, [$productId]);
        } else {
            $this->persistentSelection[] = $productId;
        }
    }

    public function isProductSelected(int $productId): bool
    {
        return in_array($productId, $this->persistentSelection);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getProductsQuery())
            ->columns([
                Tables\Columns\CheckboxColumn::make('selected')
                    ->label('')
                    ->getStateUsing(fn (Product $record) => $this->isProductSelected($record->id))
                    ->updateStateUsing(fn ($record, $state) => $this->toggleProductSelection($record->id))
                    ->width('60px')
                    ->alignCenter()
                    ->sortable(false),
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->getStateUsing(function (Product $record) {
                        $name = is_array($record->name)
                            ? ($record->name[app()->getLocale()] ?? reset($record->name))
                            : $record->name;

                        return $name;
                    }),
                TextColumn::make('category')
                    ->label('Category')
                    ->getStateUsing(function (Product $record) {
                        $category = $record->currentTenantData()?->category;
                        if (! $category) {
                            return null;
                        }

                        return is_array($category->name) ? ($category->name[app()->getLocale()] ?? reset($category->name)) : $category->name;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('eclipse-catalogue::product-status.singular'))
                    ->badge()
                    ->getStateUsing(function (Product $record) {
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();

                        $status = null;

                        if ($record->relationLoaded('productData')) {
                            $row = $record->productData
                                ->when($tenantFK && $currentTenant, fn ($c) => $c->where($tenantFK, $currentTenant->id))
                                ->first();
                            if ($row && $row->relationLoaded('status')) {
                                $status = $row->status;
                            }
                        }

                        if (! $status) {
                            return __('eclipse-catalogue::product-status.fields.no_status') ?? 'No status';
                        }

                        return is_array($status->title) ? ($status->title[app()->getLocale()] ?? reset($status->title)) : $status->title;
                    })
                    ->color(function (Product $record) {
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();

                        $status = null;
                        if ($record->relationLoaded('productData')) {
                            $row = $record->productData
                                ->when($tenantFK && $currentTenant, fn ($c) => $c->where($tenantFK, $currentTenant->id))
                                ->first();
                            if ($row && $row->relationLoaded('status')) {
                                $status = $row->status;
                            }
                        }

                        return $status?->label_type ?? 'gray';
                    })
                    ->extraAttributes(function (Product $record) {
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();

                        $status = null;
                        if ($record->relationLoaded('productData')) {
                            $row = $record->productData
                                ->when($tenantFK && $currentTenant, fn ($c) => $c->where($tenantFK, $currentTenant->id))
                                ->first();
                            if ($row && $row->relationLoaded('status')) {
                                $status = $row->status;
                            }
                        }

                        return $status ? ['class' => \Eclipse\Catalogue\Support\LabelType::badgeClass($status->label_type)] : [];
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->toggleable(),
                TextColumn::make('type.name')
                    ->label(__('eclipse-catalogue::product.table.columns.type'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('free_delivery')
                    ->label('Free Delivery')
                    ->sortable()
                    ->toggleable()
                    ->getStateUsing(function (Product $record) {
                        $tenantData = $record->currentTenantData();

                        return $tenantData?->has_free_delivery ?? false;
                    })
                    ->boolean(),
                TextColumn::make('groups')
                    ->label('Groups')
                    ->badge()
                    ->separator(',')
                    ->searchable()
                    ->toggleable()
                    ->getStateUsing(function (Product $record) {
                        $currentTenant = \Filament\Facades\Filament::getTenant();
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');

                        if ($currentTenant) {
                            return $record->groups()
                                ->where($tenantFK, $currentTenant->id)
                                ->pluck('name')
                                ->toArray();
                        }

                        return $record->groups->pluck('name')->toArray();
                    }),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categories')
                    ->multiple()
                    ->options(Category::getHierarchicalOptions())
                    ->query(function (Builder $query, array $data) {
                        $selected = $data['values'] ?? ($data['value'] ?? null);
                        if (empty($selected)) {
                            return;
                        }
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();
                        $query->whereHas('productData', function ($q) use ($selected, $tenantFK, $currentTenant) {
                            if ($tenantFK && $currentTenant) {
                                $q->where($tenantFK, $currentTenant->id);
                            }
                            $q->whereIn('category_id', (array) $selected);
                        });
                    }),
                SelectFilter::make('product_status_id')
                    ->label('Status')
                    ->multiple()
                    ->options(function () {
                        $query = ProductStatus::query();
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();

                        if ($tenantFK && $currentTenant) {
                            $query->where($tenantFK, $currentTenant->id);
                        }

                        return $query->orderBy('priority')->get()->mapWithKeys(function ($status) {
                            $title = is_array($status->title)
                                ? ($status->title[app()->getLocale()] ?? reset($status->title))
                                : $status->title;

                            return [$status->id => $title];
                        })->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        $selected = $data['values'] ?? ($data['value'] ?? null);
                        if (empty($selected)) {
                            return;
                        }
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();
                        $query->whereHas('productData', function ($q) use ($selected, $tenantFK, $currentTenant) {
                            if ($tenantFK && $currentTenant) {
                                $q->where($tenantFK, $currentTenant->id);
                            }
                            $q->whereIn('product_status_id', (array) $selected);
                        });
                    }),
                SelectFilter::make('product_type_id')
                    ->label('Product Type')
                    ->multiple()
                    ->options(function () {
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();

                        $query = ProductType::query();

                        if ($tenantFK && $currentTenant) {
                            $query->whereHas('productTypeData', function ($q) use ($tenantFK, $currentTenant) {
                                $q->where($tenantFK, $currentTenant->id)
                                    ->where('is_active', true);
                            });
                        } else {
                            $query->whereHas('productTypeData', function ($q) {
                                $q->where('is_active', true);
                            });
                        }

                        return $query->pluck('name', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        $selected = $data['values'] ?? ($data['value'] ?? null);
                        if (empty($selected)) {
                            return;
                        }
                        $query->whereIn('product_type_id', (array) $selected);
                    }),
                SelectFilter::make('free_delivery')
                    ->label('Free Delivery')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $selected = $data['values'] ?? ($data['value'] ?? null);
                        if (empty($selected)) {
                            return;
                        }
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();
                        $query->whereHas('productData', function ($q) use ($selected, $tenantFK, $currentTenant) {
                            if ($tenantFK && $currentTenant) {
                                $q->where($tenantFK, $currentTenant->id);
                            }
                            $q->whereIn('has_free_delivery', (array) $selected);
                        });
                    }),
                SelectFilter::make('groups')
                    ->label('Groups')
                    ->multiple()
                    ->relationship('groups', 'name', function ($query) {
                        $currentTenant = \Filament\Facades\Filament::getTenant();
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
                        if ($currentTenant) {
                            return $query->where($tenantFK, $currentTenant->id);
                        }

                        return $query;
                    }),
            ])
            ->filtersFormColumns(2)
            ->recordUrl(null)
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->headerActions([
                Action::make('add_selected')
                    ->label(fn () => 'Add selected products ('.count($this->persistentSelection).')')
                    ->icon('heroicon-o-plus')
                    ->disabled(fn () => empty($this->persistentSelection))
                    ->action(function () {
                        $ids = $this->persistentSelection;
                        $count = count($ids);

                        if (empty($ids)) {
                            Notification::make()
                                ->title('No products selected')
                                ->body('Please select at least one product to add.')
                                ->warning()
                                ->send();

                            return;
                        }

                        ProductRelationService::addBuffered($this->productId, $this->type, $ids);

                        Notification::make()
                            ->title('Products added successfully')
                            ->body("Added {$count} ".strtolower($this->type).' product'.($count === 1 ? '' : 's'))
                            ->success()
                            ->send();

                        $this->persistentSelection = [];

                        $this->dispatch('relations-updated');
                    }),
            ])
            ->bulkActions([])
            ->extremePaginationLinks()
            ->defaultSort('id', 'asc')
            ->striped()
            ->persistFiltersInSession();
    }

    protected function getProductsQuery(): Builder
    {
        $excludeIds = ProductRelation::query()
            ->where('parent_id', $this->productId)
            ->where('type', $this->type)
            ->pluck('child_id')
            ->toArray();

        $excludeIds[] = $this->productId;

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $currentTenant = \Filament\Facades\Filament::getTenant();

        $query = Product::query()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ])
            ->whereNotIn('id', $excludeIds)
            ->with(['type', 'groups']);

        if ($tenantFK && $currentTenant) {
            $query->with(['productData' => function ($q) use ($tenantFK, $currentTenant) {
                $q->where($tenantFK, $currentTenant->id)->with(['category', 'status']);
            }]);
        } else {
            $query->with(['productData.category', 'productData.status']);
        }

        return $query;
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function render()
    {
        return <<<'blade'
            <div class="h-[70vh] overflow-y-auto">
                {{ $this->table }}
            </div>
        blade;
    }
}
