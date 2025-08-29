<?php

namespace Eclipse\Catalogue\Forms\Components;

use Eclipse\Catalogue\Livewire\TenantSwitcher;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

/**
 * Generic per-tenant fields builder for Filament forms.
 *
 * Usage:
 * - Call GenericTenantFieldsComponent::make(...) within a Resource form schema to embed
 *   tenant-aware inputs (flags and optional extra fields) for each tenant.
 * - The component renders a tenant selector and a hidden all_tenant_data field that stores
 *   each tenant's sub-state so switching tenants does not lose edits.
 */
class GenericTenantFieldsComponent
{
    /**
     * Build a generic tenant fields section that can render arbitrary flags and fields per tenant.
     *
     * @param  array  $tenantFlags  List of boolean flag field names
     * @param  array  $mutuallyExclusiveFlagSets  Array of arrays of flags that are mutually exclusive
     * @param  string  $translationPrefix  Base translation namespace (expects .fields.* and .help_text.* keys)
     * @param  callable|null  $extraFieldsBuilder  function(int $tenantId, string $tenantName): array of extra Filament fields
     */
    public static function make(
        array $tenantFlags,
        array $mutuallyExclusiveFlagSets = [],
        string $translationPrefix = 'eclipse-catalogue::product',
        ?callable $extraFieldsBuilder = null,
        ?string $sectionTitle = null,
        ?string $sectionDescription = null,
    ): Component {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Grid::make(1)->schema([]);
        }

        $tenants = $tenantModel::all();

        return Section::make($sectionTitle ?? __('eclipse-catalogue::price-list.sections.tenant_settings'))
            ->description($sectionDescription ?? __('eclipse-catalogue::price-list.sections.tenant_settings_description'))
            ->schema([
                TenantSwitcher::make('selected_tenant'),

                // Hidden field to store all tenant data
                \Filament\Forms\Components\Hidden::make('all_tenant_data')
                    ->default([])
                    ->dehydrated(true),

                // Hidden field to track previous tenant for switching logic
                \Filament\Forms\Components\Hidden::make('_previous_tenant')
                    ->default(\Filament\Facades\Filament::getTenant()?->id)
                    ->dehydrated(false),

                ...static::getTenantSpecificFields($tenants, $tenantFlags, $mutuallyExclusiveFlagSets, $translationPrefix, $extraFieldsBuilder),
            ])
            ->collapsible()
            ->persistCollapsed();
    }

    protected static function getTenantSpecificFields($tenants, array $tenantFlags, array $exclusiveSets, string $translationPrefix, ?callable $extraFieldsBuilder): array
    {
        $fields = [];

        foreach ($tenants as $tenant) {
            $fields[] = Grid::make(1)
                ->schema(static::makeTenantFields($tenant->id, $tenant->name, $tenantFlags, $exclusiveSets, $translationPrefix, $extraFieldsBuilder))
                ->visible(fn (callable $get) => $get('selected_tenant') == $tenant->id);
        }

        return $fields;
    }

    protected static function makeTenantFields(int $tenantId, string $tenantName, array $tenantFlags, array $exclusiveSets, string $translationPrefix, ?callable $extraFieldsBuilder): array
    {
        $schema = [];

        // Render all flag toggles
        foreach ($tenantFlags as $flag) {
            $toggle = Toggle::make("tenant_data.{$tenantId}.{$flag}")
                ->label(__("{$translationPrefix}.fields.{$flag}"))
                ->helperText(__("{$translationPrefix}.help_text.{$flag}_tenant", ['tenant' => $tenantName]))
                ->inline(false)
                ->dehydrated(true)
                ->live();

            // If flag is part of any exclusive set, wire afterStateUpdated to disable others in the set
            foreach ($exclusiveSets as $set) {
                if (in_array($flag, $set, true)) {
                    $toggle = $toggle->afterStateUpdated(function ($state, callable $setField) use ($set, $flag, $tenantId) {
                        if ($state) {
                            foreach ($set as $otherFlag) {
                                if ($otherFlag !== $flag) {
                                    $setField("tenant_data.{$tenantId}.{$otherFlag}", false);
                                }
                            }
                        }
                    });
                }
            }

            // Persist current tenant's data into all_tenant_data whenever a flag changes
            $toggle = $toggle->afterStateUpdated(function ($state, callable $set, callable $get) use ($tenantId) {
                $currentData = $get("tenant_data.{$tenantId}") ?? [];
                $allTenantData = $get('all_tenant_data') ?? [];
                $allTenantData[$tenantId] = $currentData;
                $set('all_tenant_data', $allTenantData);
            });

            $schema[] = $toggle;
        }

        // Extra fields per tenant
        if ($extraFieldsBuilder) {
            $extra = $extraFieldsBuilder($tenantId, $tenantName);
            if (is_array($extra)) {
                // For extra fields, also persist on change if supported
                $enhancedExtra = [];
                foreach ($extra as $component) {
                    // Ensure extra components push state immediately while editing
                    if (method_exists($component, 'live')) {
                        $component = $component->live();
                    }
                    if (method_exists($component, 'afterStateUpdated')) {
                        $component = $component->afterStateUpdated(function ($state, callable $set, callable $get) use ($tenantId) {
                            $currentData = $get("tenant_data.{$tenantId}") ?? [];
                            $allTenantData = $get('all_tenant_data') ?? [];
                            $allTenantData[$tenantId] = $currentData;
                            $set('all_tenant_data', $allTenantData);
                        });
                    }
                    $enhancedExtra[] = $component;
                }
                $schema = [
                    ...$schema,
                    ...$enhancedExtra,
                ];
            }
        }

        return [
            Fieldset::make(__('eclipse-catalogue::product.sections.tenant_specific'))
                ->schema($schema)
                ->columns(1),
        ];
    }
}
