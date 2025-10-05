<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\GroupResource\Pages;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Common\Foundation\Models\Scopes\ActiveScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GroupResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Group::class;

    protected static ?string $slug = 'groups';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Group Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                $currentTenant = \Filament\Facades\Filament::getTenant();
                                $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
                                if ($currentTenant) {
                                    return $rule->where($tenantFK, $currentTenant->id);
                                }

                                return $rule;
                            })
                            ->helperText('Unique code for this group within the current tenant'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Whether this group is active'),

                        Toggle::make('is_browsable')
                            ->label('Browsable')
                            ->default(false)
                            ->helperText('Whether this group can be browsed in the frontend'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->getStateUsing(fn (Group $record) => $record->products_count)
                    ->sortable(false),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_browsable')
                    ->label('Browsable')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('is_browsable')
                    ->label('Browsable'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
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
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Products', [
                \Eclipse\Catalogue\Filament\Resources\GroupResource\RelationManagers\ProductsRelationManager::class,
            ]),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScope(ActiveScope::class);

        $currentTenant = \Filament\Facades\Filament::getTenant();
        if ($currentTenant) {
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key', 'site_id');
            $query->where($tenantFK, $currentTenant->id);
        }

        return $query;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'code',
            'name',
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
            'delete',
            'delete_any',
        ];
    }
}
