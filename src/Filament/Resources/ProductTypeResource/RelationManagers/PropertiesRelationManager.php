<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductTypeResource\RelationManagers;

use Eclipse\Catalogue\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'properties';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->label('Property')
                    ->options(Property::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('sort')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Property Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_global')
                    ->label('Global')
                    ->boolean(),

                Tables\Columns\TextColumn::make('max_values')
                    ->label('Max Values')
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Single' : 'Multiple'),

                Tables\Columns\IconColumn::make('is_filter')
                    ->label('Filter')
                    ->boolean(),

                Tables\Columns\TextColumn::make('values_count')
                    ->label('Values')
                    ->counts('values'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_global')
                    ->label('Global Properties'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add property')
                    ->modalHeading('Add Property')
                    ->modalSubmitActionLabel('Add Property')
                    ->modalCancelActionLabel('Cancel')
                    ->extraModalFooterActions(
                        fn (Tables\Actions\AttachAction $action): array => [
                            $action->makeModalSubmitAction('submitAnother', ['another' => true])
                                ->label('Add Property & Add Another'),
                        ]
                    )
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->options(Property::where('is_active', true)->pluck('name', 'id')->all())
                            ->searchable(),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_property')
                    ->label('Edit Property')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record): string => \Eclipse\Catalogue\Filament\Resources\PropertyResource::getUrl('edit', ['record' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\DetachAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Remove'),
                ]),
            ])
            ->defaultSort('pim_product_type_has_property.sort')
            ->reorderable('pim_product_type_has_property.sort')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering')
                    ->icon($isReordering ? 'heroicon-o-x-mark' : 'heroicon-o-arrows-up-down')
                    ->color($isReordering ? 'danger' : 'primary')
            );
    }
}
