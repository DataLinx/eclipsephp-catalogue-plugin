<?php

namespace Eclipse\Catalogue\Livewire;

use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Livewire\Component as LivewireComponent;

class TenantSwitcher extends LivewireComponent
{
    public $selectedTenant;

    public $tenants;

    public $currentTenant;

    public $fieldName;

    public $label;

    public $placeholder;

    public function mount($fieldName = 'selected_tenant', $label = null, $placeholder = null)
    {
        $this->fieldName = $fieldName;
        $this->label = $label ?? __('eclipse-catalogue::price-list.fields.tenant');
        $this->placeholder = $placeholder ?? __('eclipse-catalogue::price-list.placeholders.tenant');

        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $this->tenants = $tenantModel::all();
        $this->currentTenant = Filament::getTenant();

        // Set initial value to current tenant
        $this->selectedTenant = $this->currentTenant?->id;
    }

    public function updatedSelectedTenant($value)
    {
        $this->dispatch('tenant-changed', $value);
    }

    public function render()
    {
        return view('eclipse-catalogue::livewire.tenant-switcher');
    }

    /**
     * Create a Filament Select component used within the form schema.
     */
    public static function make(string $fieldName = 'selected_tenant'): Component
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Select::make($fieldName)->hidden();
        }

        $tenants = $tenantModel::all();
        $currentTenant = Filament::getTenant();

        return Select::make($fieldName)
            ->label(__('eclipse-catalogue::price-list.fields.tenant'))
            ->placeholder(__('eclipse-catalogue::price-list.placeholders.tenant'))
            ->options($tenants->pluck('name', 'id')->map(function ($name, $id) use ($currentTenant) {
                return $id === $currentTenant?->id
                    ? $name.' ('.__('eclipse-catalogue::price-list.labels.current').')'
                    : $name;
            }))
            ->default($currentTenant?->id)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                // Get previous tenant from a tracking field
                $previousTenant = $get('_previous_tenant');

                // Store current tenant data before switching
                if ($previousTenant && $previousTenant != $state) {
                    $currentData = $get("tenant_data.{$previousTenant}") ?? [];

                    $allTenantData = $get('all_tenant_data') ?? [];
                    $allTenantData[$previousTenant] = $currentData;
                    $set('all_tenant_data', $allTenantData);
                }

                // Update the previous tenant tracker
                $set('_previous_tenant', $state);

                // Load data for new tenant
                $allTenantData = $get('all_tenant_data') ?? [];
                if (isset($allTenantData[$state])) {
                    $set("tenant_data.{$state}", $allTenantData[$state]);
                }

                // Trigger form update when tenant changes
                $livewire->dispatch('tenant-changed', $state);
            })
            ->required();
    }

    /**
     * Same as make(), but accepts additional Select options via a map.
     */
    public static function makeWithOptions(
        string $fieldName = 'selected_tenant',
        array $options = [],
        ?string $label = null,
        ?string $placeholder = null
    ): Component {
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        if (! $tenantModel) {
            return Select::make($fieldName)->hidden();
        }

        $tenants = $tenantModel::all();
        $currentTenant = Filament::getTenant();

        $select = Select::make($fieldName)
            ->label($label ?? __('eclipse-catalogue::price-list.fields.tenant'))
            ->placeholder($placeholder ?? __('eclipse-catalogue::price-list.placeholders.tenant'))
            ->options($tenants->pluck('name', 'id')->map(function ($name, $id) use ($currentTenant) {
                return $id === $currentTenant?->id
                    ? $name.' ('.__('eclipse-catalogue::price-list.labels.current').')'
                    : $name;
            }))
            ->default($currentTenant?->id)
            ->selectablePlaceholder(false)
            ->live()
            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                $livewire->dispatch('tenant-changed', $state);
            });

        // Apply custom options
        foreach ($options as $method => $value) {
            if (method_exists($select, $method)) {
                $select->$method($value);
            }
        }

        return $select;
    }
}
