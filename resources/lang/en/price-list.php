<?php

return [
    'singular' => 'Price List',
    'plural' => 'Price Lists',

    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'currency' => 'Currency',
        'tax_included' => 'Tax Included',
        'notes' => 'Notes',
        'is_active' => 'Active',
        'is_default' => 'Default Selling',
        'is_default_purchase' => 'Default Purchase',
        'tenant' => 'Tenant',
    ],

    'sections' => [
        'information' => 'Price List Information',
        'information_description' => 'Basic information about the price list',
        'settings' => 'Settings',
        'settings_description' => 'Configure price list behavior',
        'tenant_settings' => 'Tenant Settings',
        'tenant_settings_description' => 'Configure price list settings for each tenant/site',
        'default_settings' => 'Default Settings',
    ],

    'placeholders' => [
        'name' => 'Enter price list name',
        'code' => 'Optional code for identification',
        'currency' => 'Select currency',
        'notes' => 'Optional notes about this price list',
    ],

    'help_text' => [
        'tax_included' => 'Whether prices include tax',
        'is_active' => 'Enable this price list',
        'is_active_tenant' => 'Enable this price list for :tenant',
        'is_default' => 'Use as default selling price list',
        'is_default_tenant' => 'Use as default selling price list for :tenant',
        'is_default_purchase' => 'Use as default purchase price list',
        'is_default_purchase_tenant' => 'Use as default purchase price list for :tenant',
    ],

    'table' => [
        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'currency' => 'Currency',
            'tax_included' => 'Tax Included',
            'is_active' => 'Active',
            'is_default' => 'Default Selling',
            'is_default_purchase' => 'Default Purchase',
            'created_at' => 'Created Date',
            'updated_at' => 'Last Modified Date',
        ],
    ],

    'notifications' => [
        'conflict_resolved_title' => 'Conflict Resolved',
        'conflict_resolved_selling_disabled' => 'A price list cannot be both default selling and default purchase for :tenant. Default selling has been disabled.',
        'conflict_resolved_purchase_disabled' => 'A price list cannot be both default selling and default purchase for :tenant. Default purchase has been disabled.',
        'conflict_resolved_selling_disabled_simple' => 'A price list cannot be both default selling and default purchase. Default selling has been disabled.',
        'conflict_resolved_purchase_disabled_simple' => 'A price list cannot be both default selling and default purchase. Default purchase has been disabled.',
    ],

    'validation' => [
        'cannot_be_both_defaults' => 'A price list cannot be both default selling and default purchase.',
        'cannot_be_both_defaults_tenant' => 'A price list cannot be both default selling and default purchase for :tenant.',
    ],

    'messages' => [
        'default_selling_help' => 'Only one price list can be set as default selling per tenant.',
        'default_purchase_help' => 'Only one price list can be set as default purchase per tenant.',
        'cannot_delete_default' => 'Cannot delete a default price list.',
    ],

    'labels' => [
        'current' => 'Current',
        'tenant_switcher' => 'Tenant Switcher',
    ],
];
