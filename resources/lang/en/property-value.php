<?php

return [
    'singular' => 'Property Value',
    'plural' => 'Property Values',

    'fields' => [
        'value' => 'Value',
        'info_url' => 'Info URL',
        'image' => 'Image',
        'sort' => 'Sort Order',
    ],

    'sections' => [
        'value_information' => 'Value Information',
    ],

    'placeholders' => [
        'value' => 'Enter property value',
        'info_url' => 'Enter optional "read more" link',
        'sort' => 'Enter sort order (lower numbers appear first)',
    ],

    'help_text' => [
        'info_url' => 'Optional "read more" link',
        'image' => 'Optional image for this value (e.g., brand logo)',
        'sort' => 'Lower numbers appear first',
    ],

    'table' => [
        'columns' => [
            'value' => 'Value',
            'image' => 'Image',
            'info_url' => 'Info URL',
            'sort' => 'Sort',
            'products_count' => 'Products',
            'created_at' => 'Created',
        ],
        'filters' => [
            'property' => 'Property',
        ],
        'actions' => [
            'edit' => 'Edit',
            'delete' => 'Delete',
        ],
    ],

    'modal' => [
        'create_heading' => 'Create Property Value',
        'edit_heading' => 'Edit Property Value',
    ],

    'messages' => [
        'created' => 'Property value created successfully.',
        'updated' => 'Property value updated successfully.',
        'deleted' => 'Property value deleted successfully.',
    ],

    'pages' => [
        'title' => [
            'with_property' => 'Values for: :property',
            'default' => 'Property Values',
        ],
        'breadcrumbs' => [
            'properties' => 'Properties',
            'list' => 'List',
        ],
    ],
];
