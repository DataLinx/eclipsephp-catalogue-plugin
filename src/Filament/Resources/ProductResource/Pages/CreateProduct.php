<?php

namespace Eclipse\Catalogue\Filament\Resources\ProductResource\Pages;

use Eclipse\Catalogue\Filament\Resources\Concerns\HandlesImageUploads;
use Eclipse\Catalogue\Filament\Resources\ProductResource;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Traits\HandlesTenantData;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    use HandlesImageUploads;
    use HandlesTenantData, HasTenantFields;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function getFormTenantFlags(): array
    {
        return ['is_active', 'has_free_delivery'];
    }

    protected function getFormMutuallyExclusiveFlagSets(): array
    {
        return [];
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $tenantData = $this->extractTenantDataFromFormData($data);
        $productData = $this->cleanFormDataForMainRecord($data);

        return Product::createWithTenantData($productData, $tenantData);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->action(function () {
                    $this->storeCurrentTenantData();
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }
}
