<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Forms\Components\ImageManager;
use Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;
use Eclipse\Catalogue\Forms\Components\GenericTenantFieldsComponent;
use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Traits\HandlesTenantData;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Eclipse\World\Models\Country;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource implements HasShieldPermissions
{
    use HandlesTenantData, HasTenantFields, Translatable;

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

                                        Select::make('product_type_id')
                                            ->label(__('eclipse-catalogue::product.fields.product_type'))
                                            ->relationship(
                                                'type',
                                                'name',
                                                function ($query) {
                                                    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                                                    $currentTenant = \Filament\Facades\Filament::getTenant();

                                                    if ($tenantFK && $currentTenant) {
                                                        return $query->whereHas('productTypeData', function ($q) use ($tenantFK, $currentTenant) {
                                                            $q->where($tenantFK, $currentTenant->id)
                                                                ->where('is_active', true);
                                                        });
                                                    }

                                                    return $query->whereHas('productTypeData', function ($q) {
                                                        $q->where('is_active', true);
                                                    });
                                                }
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->placeholder(__('eclipse-catalogue::product.placeholders.product_type')),

                                        RichEditor::make('short_description')
                                            ->columnSpanFull(),

                                        RichEditor::make('description')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make(__('eclipse-catalogue::product.sections.additional'))
                                    ->schema([
                                        Select::make('origin_country_id')
                                            ->label(__('eclipse-catalogue::product.fields.origin_country_id'))
                                            ->relationship('originCountry', 'name')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->id} - {$record->name}")
                                            ->searchable(['id', 'name'])
                                            ->preload()
                                            ->placeholder(__('eclipse-catalogue::product.placeholders.origin_country_id')),
                                    ])
                                    ->collapsible()
                                    ->persistCollapsed(),

                                Section::make(__('eclipse-catalogue::product.sections.seo'))
                                    ->description(__('eclipse-catalogue::product.sections.seo_description'))
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label(__('eclipse-catalogue::product.fields.meta_title'))
                                            ->maxLength(255)
                                            ->placeholder(__('eclipse-catalogue::product.placeholders.meta_title')),

                                        Textarea::make('meta_description')
                                            ->label(__('eclipse-catalogue::product.fields.meta_description'))
                                            ->rows(3)
                                            ->placeholder(__('eclipse-catalogue::product.placeholders.meta_description')),
                                    ])
                                    ->collapsible()
                                    ->persistCollapsed(),

                                // Tenant settings (embedded in General tab)
                                GenericTenantFieldsComponent::make(
                                    tenantFlags: ['is_active', 'has_free_delivery'],
                                    mutuallyExclusiveFlagSets: [],
                                    translationPrefix: 'eclipse-catalogue::product',
                                    extraFieldsBuilder: function (int $tenantId, string $tenantName) {
                                        return [
                                            Select::make("tenant_data.{$tenantId}.category_id")
                                                ->label(__('eclipse-catalogue::product.fields.category_id'))
                                                ->options(function () use ($tenantId) {
                                                    return Category::query()
                                                        ->withoutGlobalScopes()
                                                        ->where(config('eclipse-catalogue.tenancy.foreign_key', 'site_id'), $tenantId)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->placeholder(__('eclipse-catalogue::product.placeholders.category_id')),
                                            Select::make("tenant_data.{$tenantId}.groups")
                                                ->label('Groups')
                                                ->multiple()
                                                ->options(function () use ($tenantId) {
                                                    return Group::query()
                                                        ->where(config('eclipse-catalogue.tenancy.foreign_key', 'site_id'), $tenantId)
                                                        ->where('is_active', true)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->helperText('Select groups for this tenant'),
                                            TextInput::make("tenant_data.{$tenantId}.sorting_label")
                                                ->label(__('eclipse-catalogue::product.fields.sorting_label'))
                                                ->maxLength(255),
                                            \Filament\Forms\Components\DateTimePicker::make("tenant_data.{$tenantId}.available_from_date")
                                                ->label(__('eclipse-catalogue::product.fields.available_from_date')),
                                        ];
                                    },
                                    sectionTitle: __('eclipse-catalogue::product.sections.tenant_settings'),
                                    sectionDescription: __('eclipse-catalogue::product.sections.tenant_settings_description'),
                                ),
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

                ImageColumn::make('cover_image')
                    ->stacked()
                    ->label('Image')
                    ->getStateUsing(function (Product $record) {
                        $url = $record->getFirstMediaUrl('images', 'thumb');

                        return $url ?: null;
                    })
                    ->circular()
                    ->defaultImageUrl(static::getPlaceholderImageUrl())
                    ->extraImgAttributes(function (Product $record) {
                        $coverMedia = $record->getMedia('images')
                            ->filter(fn ($media) => $media->getCustomProperty('is_cover', false))
                            ->first();

                        if (! $coverMedia) {
                            $coverMedia = $record->getMedia('images')->first();
                        }

                        $fullImageUrl = $coverMedia ? $coverMedia->getUrl() : null;
                        $imageName = $coverMedia ? json_encode($coverMedia->getCustomProperty('name', [])) : '{}';
                        $imageDescription = $coverMedia ? json_encode($coverMedia->getCustomProperty('description', [])) : '{}';

                        return [
                            'class' => 'cursor-pointer product-image-trigger',
                            'data-url' => $fullImageUrl ?: static::getPlaceholderImageUrl(),
                            'data-image-name' => htmlspecialchars($imageName, ENT_QUOTES, 'UTF-8'),
                            'data-image-description' => htmlspecialchars($imageDescription, ENT_QUOTES, 'UTF-8'),
                            'data-product-name' => htmlspecialchars(json_encode($record->getTranslations('name')), ENT_QUOTES, 'UTF-8'),
                            'data-product-code' => $record->code ?: '',
                            'data-filename' => $coverMedia ? $coverMedia->file_name : '',
                            'onclick' => 'event.stopPropagation(); return false;',
                        ];
                    }),

                TextColumn::make('name')
                    ->toggleable(false),

                TextColumn::make('category')
                    ->label('Category')
                    ->getStateUsing(function (Product $record) {
                        $category = $record->currentTenantData()?->category;
                        if (! $category) {
                            return null;
                        }

                        return is_array($category->name) ? ($category->name[app()->getLocale()] ?? reset($category->name)) : $category->name;
                    }),

                TextColumn::make('type.name')
                    ->label(__('eclipse-catalogue::product.table.columns.type')),

                TextColumn::make('groups.name')
                    ->label('Groups')
                    ->badge()
                    ->separator(',')
                    ->limit(3)
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

                IconColumn::make('is_active')
                    ->label(__('eclipse-catalogue::product.table.columns.is_active'))
                    ->boolean(),

                TextColumn::make('originCountry.name')
                    ->label(__('eclipse-catalogue::product.fields.origin_country_id')),

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
                SelectFilter::make('product_type_id')
                    ->label(__('eclipse-catalogue::product.filters.product_type'))
                    ->multiple()
                    ->options(function () {
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                        $currentTenant = \Filament\Facades\Filament::getTenant();

                        $query = \Eclipse\Catalogue\Models\ProductType::query();

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
                    }),
                SelectFilter::make('origin_country_id')
                    ->label(__('eclipse-catalogue::product.fields.origin_country_id'))
                    ->multiple()
                    ->options(fn () => Country::query()->orderBy('name')->pluck('name', 'id')->toArray()),
                SelectFilter::make('groups')
                    ->label('Groups')
                    ->multiple()
                    ->relationship('groups', 'name', function ($query) {
                        $currentTenant = \Filament\Facades\Filament::getTenant();
                        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
                        if ($currentTenant) {
                            return $query->where($tenantFK, $currentTenant->id)
                                ->where('is_active', true);
                        }

                        return $query->where('is_active', true);
                    }),
                TernaryFilter::make('is_active')
                    ->label(__('eclipse-catalogue::product.table.columns.is_active'))
                    ->queries(
                        true: function (Builder $query) {
                            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                            $currentTenant = \Filament\Facades\Filament::getTenant();

                            return $query->whereHas('productData', function ($q) use ($tenantFK, $currentTenant) {
                                $q->where('is_active', true);
                                if ($tenantFK && $currentTenant) {
                                    $q->where($tenantFK, $currentTenant->id);
                                }
                            });
                        },
                        false: function (Builder $query) {
                            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                            $currentTenant = \Filament\Facades\Filament::getTenant();

                            return $query->whereHas('productData', function ($q) use ($tenantFK, $currentTenant) {
                                $q->where('is_active', false);
                                if ($tenantFK && $currentTenant) {
                                    $q->where($tenantFK, $currentTenant->id);
                                }
                            });
                        },
                    ),
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
                    BulkAction::make('add_to_group')
                        ->label('Add to Group')
                        ->icon('heroicon-o-plus')
                        ->form([
                            Select::make('group_id')
                                ->label('Group')
                                ->options(function () {
                                    $currentTenant = \Filament\Facades\Filament::getTenant();
                                    $query = Group::query()->where('is_active', true);
                                    if ($currentTenant) {
                                        $query->where('site_id', $currentTenant->id);
                                    }

                                    return $query->pluck('name', 'id')->toArray();
                                })
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, $records) {
                            $group = Group::find($data['group_id']);
                            $addedCount = 0;

                            foreach ($records as $product) {
                                if (! $group->hasProduct($product)) {
                                    $group->addProduct($product);
                                    $addedCount++;
                                }
                            }

                            Notification::make()
                                ->title("Added {$addedCount} products to group \"{$group->name}\"")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('remove_from_group')
                        ->label('Remove from Group')
                        ->icon('heroicon-o-minus')
                        ->form([
                            Select::make('group_id')
                                ->label('Group')
                                ->options(function () {
                                    $currentTenant = \Filament\Facades\Filament::getTenant();
                                    $query = Group::query()->where('is_active', true);
                                    if ($currentTenant) {
                                        $query->where('site_id', $currentTenant->id);
                                    }

                                    return $query->pluck('name', 'id')->toArray();
                                })
                                ->required()
                                ->searchable(),
                        ])
                        ->action(function (array $data, $records) {
                            $group = Group::find($data['group_id']);
                            $removedCount = 0;

                            foreach ($records as $product) {
                                if ($group->hasProduct($product)) {
                                    $group->removeProduct($product);
                                    $removedCount++;
                                }
                            }

                            Notification::make()
                                ->title("Removed {$removedCount} products from group \"{$group->name}\"")
                                ->success()
                                ->send();
                        }),
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
            'view' => Pages\ViewProduct::route('/{record}'),
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

    protected static function getPlaceholderImageUrl(): string
    {
        $svg = view('eclipse-catalogue::components.placeholder-image')->render();

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
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
