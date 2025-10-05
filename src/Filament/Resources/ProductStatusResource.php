<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\ProductStatusResource\Pages;
use Eclipse\Catalogue\Models\ProductStatus;
use Eclipse\Catalogue\Support\LabelType;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ProductStatusResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = ProductStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $slug = 'product-statuses';

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('eclipse-catalogue::product-status.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('eclipse-catalogue::product-status.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('eclipse-catalogue::product-status.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('eclipse-catalogue::product-status.singular'))
                ->schema([
                    TextInput::make('title')->label(__('eclipse-catalogue::product-status.fields.title'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.title'))
                        ->required()->maxLength(255),
                    Grid::make(3)->schema([
                        TextInput::make('code')->label(__('eclipse-catalogue::product-status.fields.code'))
                            ->helperText(__('eclipse-catalogue::product-status.help_text.code'))
                            ->nullable()
                            ->maxLength(20)
                            ->unique(
                                table: 'pim_product_statuses',
                                column: 'code',
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule, $livewire) {
                                    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
                                    if ($tenantFK) {
                                        $currentTenant = \Filament\Facades\Filament::getTenant();
                                        if ($currentTenant) {
                                            $rule->where($tenantFK, $currentTenant->id);
                                        }
                                    }

                                    return $rule;
                                }
                            )
                            ->validationMessages([
                                'unique' => __('eclipse-catalogue::product-status.validation.code_unique'),
                            ]),
                        Select::make('label_type')
                            ->label(__('eclipse-catalogue::product-status.fields.label_type'))
                            ->helperText(__('eclipse-catalogue::product-status.help_text.label_type'))
                            ->options(LabelType::options())
                            ->default('gray')
                            ->required(),
                        TextInput::make('priority')->label(__('eclipse-catalogue::product-status.fields.priority'))
                            ->helperText(__('eclipse-catalogue::product-status.help_text.priority'))
                            ->numeric()->required(),
                    ]),
                    Textarea::make('description')->label(__('eclipse-catalogue::product-status.fields.description'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.description'))
                        ->rows(3),
                ])->columns(1),
            Section::make(__('eclipse-catalogue::product-status.sections.visibility_rules'))->schema([
                Grid::make(3)->schema([
                    \Filament\Forms\Components\Toggle::make('shown_in_browse')->label(__('eclipse-catalogue::product-status.fields.shown_in_browse'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.shown_in_browse'))
                        ->default(true),
                    \Filament\Forms\Components\Toggle::make('allow_price_display')->label(__('eclipse-catalogue::product-status.fields.allow_price_display'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.allow_price_display'))
                        ->default(true)
                        ->live(),
                    \Filament\Forms\Components\Toggle::make('allow_sale')->label(__('eclipse-catalogue::product-status.fields.allow_sale'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.allow_sale'))
                        ->default(true)
                        ->disabled(fn ($get) => $get('allow_price_display') === false),
                    \Filament\Forms\Components\Toggle::make('is_default')->label(__('eclipse-catalogue::product-status.fields.is_default'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.is_default'))
                        ->default(false),
                    \Filament\Forms\Components\Toggle::make('skip_stock_qty_check')->label(__('eclipse-catalogue::product-status.fields.skip_stock_qty_check'))
                        ->helperText(__('eclipse-catalogue::product-status.help_text.skip_stock_qty_check'))
                        ->default(false),
                    Select::make('sd_item_availability')->label(__('eclipse-catalogue::product-status.fields.sd_item_availability'))
                        ->helperText(new HtmlString(__('eclipse-catalogue::product-status.help_text.sd_item_availability')))
                        ->options(\Eclipse\Catalogue\Enums\StructuredData\ItemAvailability::class)
                        ->searchable()
                        ->required(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label(__('eclipse-catalogue::product-status.fields.code'))->searchable(),
            BadgeColumn::make('title')->label(__('eclipse-catalogue::product-status.fields.title'))
                ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? reset($state)) : $state)
                ->color(fn (ProductStatus $record) => $record->label_type)
                ->extraAttributes(fn (ProductStatus $record) => ['class' => LabelType::badgeClass($record->label_type)]),
            IconColumn::make('shown_in_browse')->label(__('eclipse-catalogue::product-status.fields.shown_in_browse'))->boolean(),
            IconColumn::make('allow_price_display')->label(__('eclipse-catalogue::product-status.fields.allow_price_display'))->boolean(),
            IconColumn::make('allow_sale')->label(__('eclipse-catalogue::product-status.fields.allow_sale'))->boolean(),
            IconColumn::make('is_default')->label(__('eclipse-catalogue::product-status.fields.is_default'))->boolean(),
            TextColumn::make('priority')->label(__('eclipse-catalogue::product-status.fields.priority'))->numeric(),
        ])->filters([
            TernaryFilter::make('shown_in_browse')->label(__('eclipse-catalogue::product-status.fields.shown_in_browse')),
        ])->actions([
            \Filament\Tables\Actions\EditAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductStatuses::route('/'),
            'create' => Pages\CreateProductStatus::route('/create'),
            'edit' => Pages\EditProductStatus::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filter by current tenant if tenancy is enabled
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        if ($tenantFK) {
            $currentTenant = \Filament\Facades\Filament::getTenant();
            if ($currentTenant) {
                $query->where($tenantFK, $currentTenant->id);
            }
        }

        return $query;
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
        ];
    }
}
