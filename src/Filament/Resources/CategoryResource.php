<?php

namespace Eclipse\Catalogue\Filament\Resources;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Eclipse\Catalogue\Filament\Resources\CategoryResource\Pages;
use Eclipse\Catalogue\Models\Category;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource implements HasShieldPermissions
{
    use Translatable;

    protected static ?string $model = Category::class;

    protected static ?string $slug = 'catalogue/categories';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter category name'),

                                TextInput::make('code')
                                    ->maxLength(255)
                                    ->placeholder('Enter category code'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('parent_id')
                                    ->label('Parent Category')
                                    ->options(Category::getHierarchicalOptions())
                                    ->searchable()
                                    ->placeholder('Select parent category (optional)'),

                                TextInput::make('sef_key')
                                    ->label('SEF Key')
                                    ->maxLength(255)
                                    ->placeholder('URL-friendly key (auto-generated if empty)')
                                    ->helperText('Leave empty to auto-generate from category name'),
                            ]),
                    ]),

                Section::make('Content')
                    ->compact()
                    ->schema([
                        Textarea::make('short_desc')
                            ->label('Short Description')
                            ->rows(3)
                            ->placeholder('Enter a brief description'),

                        RichEditor::make('description')
                            ->label('Full Description')
                            ->placeholder('Enter detailed category description')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ]),
                    ]),

                Section::make('Media & Settings')
                    ->compact()
                    ->schema([
                        FileUpload::make('image')
                            ->columnSpanFull()
                            ->label('Category Image')
                            ->image()
                            ->imageEditor()
                            ->directory('categories')
                            ->visibility('public'),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Whether this category is visible'),

                                Toggle::make('recursive_browsing')
                                    ->label('Recursive Browsing')
                                    ->default(false)
                                    ->helperText('Allow browsing subcategories recursively'),
                            ]),
                    ]),

                Section::make('System Information')
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Created Date')
                                    ->content(fn (?Category $record): string => $record?->created_at?->diffForHumans() ?? 'Not yet saved'),

                                Placeholder::make('updated_at')
                                    ->label('Last Modified Date')
                                    ->content(fn (?Category $record): string => $record?->updated_at?->diffForHumans() ?? 'Not yet saved'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function ($record, $state): string {
                        $level = $record->parent_id ? '└─ ' : '';

                        return $level.$state;
                    }),

                TextColumn::make('sef_key')
                    ->label('SEF Key')
                    ->searchable()
                    ->fontFamily('mono')
                    ->copyable()
                    ->size('sm'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('code')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('No code'),

                IconColumn::make('recursive_browsing')
                    ->label('Recursive')
                    ->boolean()
                    ->tooltip('Include products from subcategories'),

                IconColumn::make('description')
                    ->label('Long Desc.')
                    ->getStateUsing(fn ($record) => ! empty($record->description))
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : 'heroicon-o-document')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->tooltip(fn ($record) => ! empty($record->description) ? 'Has description' : 'No description'),

                TextColumn::make('short_desc'),

                TextColumn::make('sort')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->searchable()
            ->reorderable('sort')
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->relationship('parent', 'name')
                    ->placeholder('All Categories'),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('All Statuses'),

                Filter::make('has_description')
                    ->label('Has Description')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('description'))
                    ->toggle(),

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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'sorting' => Pages\SortingCategory::route('/sorting'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('site_id', Filament::getTenant()->id)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'short_desc', 'description', 'sef_key', 'code'];
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
