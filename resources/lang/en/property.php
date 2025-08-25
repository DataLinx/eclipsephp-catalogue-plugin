<?php

return [
    'singular' => 'Property',
    'plural' => 'Properties',

    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'description' => 'Description',
        'internal_name' => 'Internal Name',
        'is_active' => 'Active',
        'is_global' => 'Global Property',
        'max_values' => 'Maximum Values',
        'enable_sorting' => 'Enable Manual Sorting',
        'is_filter' => 'Show as Filter',
        'product_types' => 'Assign to Product Types',
    ],

    'sections' => [
        'basic_information' => 'Basic Information',
        'configuration' => 'Configuration',
        'product_types' => 'Product Types',
    ],

    'placeholders' => [
        'name' => 'Enter property name',
        'code' => 'Optional alphanumeric code with underscores',
        'description' => 'Enter property description',
        'internal_name' => 'Enter internal name for distinction',
    ],

    'help_text' => [
        'code' => 'Optional alphanumeric code with underscores, automatically converted to lowercase',
        'internal_name' => 'Internal name for distinction, not translatable',
        'is_global' => 'Auto-assigned to all product types',
        'max_values' => 'Maximum number of values allowed for this property (1 = single value, 2+ = multiple values)',
        'enable_sorting' => 'Allow drag-and-drop sorting of property values',
        'is_filter' => 'Display property as filter in product table',
        'product_types' => 'Select product types for this property (ignored if Global is enabled)',
    ],

    'table' => [
        'columns' => [
            'code' => 'Code',
            'name' => 'Name',
            'internal_name' => 'Internal Name',
            'is_global' => 'Global',
            'max_values' => 'Max Values',
            'enable_sorting' => 'Sortable',
            'is_filter' => 'Filter',
            'is_active' => 'Active',
            'values_count' => 'Values',
            'created_at' => 'Created',
        ],
        'filters' => [
            'product_type' => 'Product Type',
            'is_global' => 'Global Properties',
            'is_active' => 'Active Properties',
            'is_filter' => 'Filter Properties',
        ],
        'actions' => [
            'values' => 'Values',
            'edit' => 'Edit',
            'delete' => 'Delete',
        ],
    ],

    'messages' => [
        'created' => 'Property created successfully.',
        'updated' => 'Property updated successfully.',
        'deleted' => 'Property deleted successfully.',
    ],
];
