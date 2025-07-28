<?php

return [
    'title' => 'Categories',
    'navigation_group' => 'Catalogue',
    'sorting' => 'Sorting',

    'form' => [
        'sections' => [
            'basic_information' => 'Basic Information',
            'content' => 'Content',
            'media_settings' => 'Media & Settings',
            'system_information' => 'System Information',
        ],
        'fields' => [
            'parent_id' => 'Parent Category',
            'parent_id_placeholder' => 'Select parent category (optional)',
            'name' => 'Name',
            'name_placeholder' => 'Enter category name',
            'code' => 'Code',
            'code_placeholder' => 'Enter category code',
            'sef_key' => 'SEF Key',
            'sef_key_placeholder' => 'URL-friendly key (auto-generated if empty)',
            'sef_key_helper' => 'Leave empty to auto-generate from category name',
            'short_desc' => 'Short Description',
            'short_desc_placeholder' => 'Enter a brief description',
            'description' => 'Full Description',
            'description_placeholder' => 'Enter detailed category description',
            'image' => 'Category Image',
            'is_active' => 'Active',
            'is_active_helper' => 'Whether this category is visible',
            'recursive_browsing' => 'Recursive Browsing',
            'recursive_browsing_helper' => 'Allow browsing subcategories recursively',
            'created_at' => 'Created Date',
            'updated_at' => 'Last Modified Date',
            'not_yet_saved' => 'Not yet saved',
        ],
        'errors' => [
            'sef_key' => 'The SEF key has already been taken.',
            'parent_id' => 'Cannot select this category as parent - it would create a circular reference',
        ],
    ],

    'table' => [
        'columns' => [
            'image' => 'Image',
            'name' => 'Category',
            'sef_key' => 'SEF Key',
            'is_active' => 'Active',
            'code' => 'Code',
            'recursive_browsing' => 'Recursive',
            'description' => 'Long Desc.',
            'short_desc' => 'Short Description',
        ],
        'tooltips' => [
            'recursive_browsing' => 'Include products from subcategories',
            'has_description' => 'Has description',
            'no_description' => 'No description',
        ],
    ],

    'filters' => [
        'parent_category' => 'Parent Category',
        'category_placeholder' => 'All Categories',
        'is_active' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'all_statuses' => 'All Statuses',
        'has_description' => 'Has Description',
    ],

    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
        'sorting' => 'Sorting',
        'bulk_actions' => 'Bulk Actions',
    ],
];
