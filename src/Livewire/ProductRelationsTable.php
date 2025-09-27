<?php

namespace Eclipse\Catalogue\Livewire;

use Eclipse\Catalogue\Enums\ProductRelationType;
use Eclipse\Catalogue\Models\ProductRelation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ProductRelationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public int $productId;

    public string $type = ProductRelationType::RELATED->value;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    public function mount(int $productId, string $type): void
    {
        $this->productId = $productId;
        $this->type = $type;
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query($this->getRelationsQuery())
            ->columns([
                TextColumn::make('child_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (ProductRelation $record) {
                        return $record->child?->code ?? 'N/A';
                    }),
                TextColumn::make('child_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (ProductRelation $record) {
                        if (! $record->child) {
                            return 'N/A';
                        }
                        $name = is_array($record->child->name)
                            ? ($record->child->name[app()->getLocale()] ?? reset($record->child->name))
                            : $record->child->name;

                        return $name;
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('add')
                    ->label('Add products')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Select products')
                    ->modalWidth('full')
                    ->modalContent(fn () => new \Illuminate\Support\HtmlString(
                        \Livewire\Livewire::mount('eclipse.catalogue.livewire.product-selector-table', [
                            'productId' => $this->productId,
                            'type' => $this->type,
                        ])
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cancel')
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_product')
                    ->label('Edit Product')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record): string => \Eclipse\Catalogue\Filament\Resources\ProductResource::getUrl('edit', ['record' => $record->child_id]))
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make()
                    ->label('Remove')
                    ->modalHeading(fn ($record) => 'Remove '.($record->child?->name ?? 'product'))
                    ->modalSubmitActionLabel('Remove')
                    ->modalCancelActionLabel('Cancel'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Remove')
                        ->modalHeading('Remove selected')
                        ->modalSubmitActionLabel('Remove')
                        ->modalCancelActionLabel('Cancel'),
                ]),
            ])
            ->reorderable('sort')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering')
                    ->icon($isReordering ? 'heroicon-o-x-mark' : 'heroicon-o-arrows-up-down')
                    ->color($isReordering ? 'danger' : 'primary')
            )
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    protected function getRelationsQuery(): Builder
    {
        return ProductRelation::query()
            ->where('parent_id', $this->productId)
            ->where('type', $this->type)
            ->with('child')
            ->orderBy('sort')
            ->orderBy('id');
    }

    public function render()
    {
        return <<<'blade'
            <div>
                {{ $this->table }}
            </div>
        blade;
    }
}
