<?php

return [
    'singular' => 'Product Type',
    'plural' => 'Product Types',

    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'is_active' => 'Active',
        'is_default' => 'Default Type',
        'tenant' => 'Tenant',
    ],

    'sections' => [
        'information' => 'Product Type Information',
        'information_description' => 'Basic information about the product type',
        'settings' => 'Settings',
        'settings_description' => 'Configure product type behavior',
        'tenant_settings' => 'Tenant Settings',
        'tenant_settings_description' => 'Configure product type settings for each tenant/site',
        'default_settings' => 'Default Settings',
    ],

    'placeholders' => [
        'name' => 'Enter product type name',
        'code' => 'Optional code for identification',
        'tenant' => 'Select tenant',
    ],

    'help_text' => [
        'is_active' => 'Enable this product type',
        'is_active_tenant' => 'Enable this product type for :tenant',
        'is_default' => 'Use as default product type',
        'is_default_tenant' => 'Use as default product type for :tenant',
    ],

    'table' => [
        'columns' => [
            'id' => 'ID',
            'name' => 'Name',
            'code' => 'Code',
            'is_active' => 'Active',
            'is_default' => 'Default Type',
            'created_at' => 'Created Date',
            'updated_at' => 'Last Modified Date',
        ],
    ],

    'notifications' => [
        'conflict_resolved_title' => 'Conflict Resolved',
        'conflict_resolved_is_default_disabled' => 'Only one product type can be default for :tenant. Previous default has been disabled.',
    ],

    'validation' => [
        'only_one_default_per_tenant' => 'Only one product type can be set as default per tenant.',
    ],

    'messages' => [
        'default_help' => 'Only one product type can be set as default per tenant.',
        'cannot_delete_default' => 'Cannot delete a default product type.',
    ],

    'labels' => [
        'current' => 'Current',
        'tenant_switcher' => 'Tenant Switcher',
    ],
];
