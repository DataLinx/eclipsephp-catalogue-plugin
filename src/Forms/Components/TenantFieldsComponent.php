<?php

namespace Eclipse\Catalogue\Forms\Components;

use Eclipse\Catalogue\Livewire\TenantSwitcher;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

class TenantFieldsComponent
{
    public static function make(): Component
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Grid::make(1)->schema([]);
        }

        $tenants = $tenantModel::all();

        return Section::make(__('eclipse-catalogue::price-list.sections.tenant_settings'))
            ->description(__('eclipse-catalogue::price-list.sections.tenant_settings_description'))
            ->schema([
                TenantSwitcher::make('selected_tenant'),

                // Hidden field to store all tenant data
                \Filament\Forms\Components\Hidden::make('all_tenant_data')
                    ->default([])
                    ->dehydrated(true),

                // Hidden field to track previous tenant for switching logic
                \Filament\Forms\Components\Hidden::make('_previous_tenant')
                    ->default(\Filament\Facades\Filament::getTenant()?->id)
                    ->dehydrated(false), // Don't submit this field

                ...static::getTenantSpecificFields($tenants),
            ])
            ->collapsible()
            ->persistCollapsed();
    }

    /**
     * Create a tenant switcher component that can be used independently.
     */
    public static function makeSwitcher(string $fieldName = 'selected_tenant'): Component
    {
        return TenantSwitcher::make($fieldName);
    }

    /**
     * Build the fields that belong to a single tenant.
     */
    public static function makeTenantFields(int $tenantId, string $tenantName): array
    {
        return [
            Toggle::make("tenant_data.{$tenantId}.is_active")
                ->label(__('eclipse-catalogue::price-list.fields.is_active'))
                ->helperText(__('eclipse-catalogue::price-list.help_text.is_active_tenant', ['tenant' => $tenantName]))
                ->default(true)
                ->inline(false)
                ->dehydrated(true), // Always include in form data

            Fieldset::make(__('eclipse-catalogue::price-list.sections.default_settings'))
                ->schema([
                    Toggle::make("tenant_data.{$tenantId}.is_default")
                        ->label(__('eclipse-catalogue::price-list.fields.is_default'))
                        ->helperText(__('eclipse-catalogue::price-list.help_text.is_default_tenant', ['tenant' => $tenantName]))
                        ->inline(false)
                        ->live()
                        ->dehydrated(true) // Always include in form data
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($tenantId, $tenantName) {
                            if ($state && $get("tenant_data.{$tenantId}.is_default_purchase")) {
                                $set("tenant_data.{$tenantId}.is_default_purchase", false);
                                Notification::make()
                                    ->warning()
                                    ->title(__('eclipse-catalogue::price-list.notifications.conflict_resolved_title'))
                                    ->body(__('eclipse-catalogue::price-list.notifications.conflict_resolved_purchase_disabled', ['tenant' => $tenantName]))
                                    ->send();
                            }
                        }),

                    Toggle::make("tenant_data.{$tenantId}.is_default_purchase")
                        ->label(__('eclipse-catalogue::price-list.fields.is_default_purchase'))
                        ->helperText(__('eclipse-catalogue::price-list.help_text.is_default_purchase_tenant', ['tenant' => $tenantName]))
                        ->inline(false)
                        ->live()
                        ->dehydrated(true) // Always include in form data
                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($tenantId, $tenantName) {
                            if ($state && $get("tenant_data.{$tenantId}.is_default")) {
                                $set("tenant_data.{$tenantId}.is_default", false);
                                Notification::make()
                                    ->warning()
                                    ->title(__('eclipse-catalogue::price-list.notifications.conflict_resolved_title'))
                                    ->body(__('eclipse-catalogue::price-list.notifications.conflict_resolved_selling_disabled', ['tenant' => $tenantName]))
                                    ->send();
                            }
                        }),
                ])
                ->columns(1),
        ];
    }

    /**
     * Wrap each tenant's fields in a Grid and show only the selected tenant's
     * fields. Values are still dehydrated via per-field settings.
     */
    protected static function getTenantSpecificFields($tenants): array
    {
        $fields = [];

        foreach ($tenants as $tenant) {
            $fields[] = Grid::make(1)
                ->schema(static::makeTenantFields($tenant->id, $tenant->name))
                ->visible(fn (callable $get) => $get('selected_tenant') == $tenant->id);
        }

        return $fields;
    }
}
