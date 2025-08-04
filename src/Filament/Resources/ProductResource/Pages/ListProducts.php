<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Common\Foundation\Pages\HasScoutSearch;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\View\View;

class ListProducts extends ListRecords
{
    use HasScoutSearch, Translatable;

    protected static string $resource = ProductResource::class;

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }

    public function getFooter(): ?View
    {
        return view('eclipse-catalogue::filament.resources.product-resource.pages.list-products-footer');
    }
}
