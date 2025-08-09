<?php

namespace Eclipse\Catalogue\Filament\Resources\PriceListResource\Pages;

use Eclipse\Catalogue\Filament\Resources\PriceListResource;
use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Traits\HandlesPriceListTenantData;
use Eclipse\Catalogue\Traits\HasPriceListForm;
use Eclipse\Catalogue\Traits\HasTenantFields;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePriceList extends CreateRecord
{
    use HandlesPriceListTenantData, HasPriceListForm, HasTenantFields;

    protected static string $resource = PriceListResource::class;

    public function form(Form $form): Form
    {
        return $form->schema($this->buildPriceListFormSchema());
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Extract tenant data from form
        $tenantData = $this->extractTenantDataFromFormData($data);

        // Clean main record data
        $priceListData = $this->cleanFormDataForMainRecord($data);

        // Use the model's createWithTenantData method
        return PriceList::createWithTenantData($priceListData, $tenantData);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->action(function () {
                    // Store current tenant data before validation/saving
                    $this->storeCurrentTenantData();
                    $this->validateDefaultConstraintsBeforeSave();
                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }
}
