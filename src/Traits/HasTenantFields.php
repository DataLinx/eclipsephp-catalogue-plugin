<?php

namespace Eclipse\Catalogue\Traits;

use Eclipse\Catalogue\Forms\Components\TenantFieldsComponent;

trait HasTenantFields
{
    /**
     * Add tenant fields to a form schema
     */
    protected function addTenantFields(array $schema): array
    {
        $tenantModel = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantModel) {
            return $schema;
        }

        return [
            ...$schema,
            TenantFieldsComponent::make(),
        ];
    }

    /**
     * Add only the tenant switcher to a form schema
     */
    protected function addTenantSwitcher(array $schema, string $fieldName = 'selected_tenant'): array
    {
        $tenantModel = config('eclipse-catalogue.tenancy.foreign_key');

        if (! $tenantModel) {
            return $schema;
        }

        return [
            ...$schema,
            TenantFieldsComponent::makeSwitcher($fieldName),
        ];
    }

    /**
     * Add tenant-specific fields for a specific tenant
     */
    protected function addTenantSpecificFields(array $schema, int $tenantId, string $tenantName): array
    {
        return [
            ...$schema,
            ...TenantFieldsComponent::makeTenantFields($tenantId, $tenantName),
        ];
    }

    /**
     * Check if tenancy is enabled
     */
    protected function isTenancyEnabled(): bool
    {
        return (bool) config('eclipse-catalogue.tenancy.foreign_key');
    }

    /**
     * Get the current tenant ID
     */
    protected function getCurrentTenantId(): ?int
    {
        return \Filament\Facades\Filament::getTenant()?->id;
    }
}
