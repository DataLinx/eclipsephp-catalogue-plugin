@php
    $record = $this->getRecord() ?? null;
    $managerClass = \Eclipse\Catalogue\Filament\Resources\ProductResource\RelationManagers\PricesRelationManager::class;
@endphp

<div>
    @if ($record)
        @livewire($managerClass, [
            'ownerRecord' => $record,
            'pageClass' => \Eclipse\Catalogue\Filament\Resources\ProductResource\Pages\EditProduct::class,
        ])
    @else
        <x-filament::section.heading class="text-sm text-gray-500">Save the product first to manage prices.</x-filament::section.heading>
    @endif
</div>


