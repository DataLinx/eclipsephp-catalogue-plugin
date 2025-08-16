<?php

namespace Eclipse\Catalogue\Traits;

use Eclipse\Catalogue\Models\PriceList;

trait HandlesPriceListTenantData
{
    /**
     * Persist the currently selected tenant's values into the hidden
     * all_tenant_data state so switching tenants doesn't lose changes.
     */
    protected function storeCurrentTenantData(): void
    {
        $formData = $this->form->getState();
        $selectedTenant = $formData['selected_tenant'] ?? null;

        if ($selectedTenant && config('eclipse-catalogue.tenancy.foreign_key')) {
            $currentData = [
                'is_active' => $formData['tenant_data'][$selectedTenant]['is_active'] ?? true,
                'is_default' => $formData['tenant_data'][$selectedTenant]['is_default'] ?? false,
                'is_default_purchase' => $formData['tenant_data'][$selectedTenant]['is_default_purchase'] ?? false,
            ];

            $allTenantData = $formData['all_tenant_data'] ?? [];
            $allTenantData[$selectedTenant] = $currentData;

            $this->form->fill(['all_tenant_data' => $allTenantData]);
        }
    }

    /**
     * Validate default constraints before saving.
     */
    protected function validateDefaultConstraintsBeforeSave(): void
    {
        $data = $this->form->getState();
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - validate simple fields
            $tenantData = [
                'is_default' => $data['is_default'] ?? false,
                'is_default_purchase' => $data['is_default_purchase'] ?? false,
            ];
            PriceList::validateTenantDataConstraints($tenantData);

            return;
        }

        // Validate tenant data (multi-tenant)
        $tenantData = $data['tenant_data'] ?? [];
        PriceList::validateTenantDataConstraints($tenantData);

        // Handle form state for first error tenant (for UI feedback)
        $firstErrorTenantId = $this->getFirstErrorTenantId($tenantData);
        if ($firstErrorTenantId) {
            $this->form->fill(['selected_tenant' => $firstErrorTenantId]);
        }
    }

    /**
     * Get the first tenant ID that has validation errors.
     */
    private function getFirstErrorTenantId(array $tenantData): ?int
    {
        foreach ($tenantData as $tenantId => $tenantSpecificData) {
            if (
                ($tenantSpecificData['is_default'] ?? false) &&
                ($tenantSpecificData['is_default_purchase'] ?? false)
            ) {
                return $tenantId;
            }
        }

        return null;
    }

    /**
     * Extract per-tenant data from the form state.
     */
    protected function extractTenantDataFromFormData(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // No tenancy - return simple fields
            return [
                'is_active' => $data['is_active'] ?? true,
                'is_default' => $data['is_default'] ?? false,
                'is_default_purchase' => $data['is_default_purchase'] ?? false,
            ];
        }

        // Get stored tenant data and current form data
        $storedTenantData = $data['all_tenant_data'] ?? [];
        $currentTenantData = $data['tenant_data'] ?? [];
        $selectedTenant = $data['selected_tenant'] ?? null;

        // Merge stored data with current form data
        $result = $storedTenantData;

        // If we have a selected tenant, store its current form data
        if ($selectedTenant && isset($currentTenantData[$selectedTenant])) {
            $result[$selectedTenant] = $currentTenantData[$selectedTenant];
        }

        return $result;
    }

    /**
     * Remove UI-only fields from the form values so we can safely pass
     * the remaining payload to the base PriceList model.
     */
    protected function cleanFormDataForMainRecord(array $data): array
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantFK) {
            // Remove simple tenant fields
            unset($data['is_active'], $data['is_default'], $data['is_default_purchase']);
        } else {
            // Remove tenant UI fields
            unset($data['tenant_data'], $data['selected_tenant'], $data['all_tenant_data']);
        }

        return $data;
    }
}
