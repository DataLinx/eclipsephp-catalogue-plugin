<?php

return [
    'singular' => 'Property Value',
    'plural' => 'Property Values',

    'fields' => [
        'value' => 'Value',
        'info_url' => 'Info URL',
        'image' => 'Image',
        'sort' => 'Sort Order',
        'import_file' => 'Import File',
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
        'import_file' => 'Upload an Excel (.xlsx, .xls) or CSV file with two columns: name and hex. Example: Red, #FF0000',
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

    'actions' => [
        'import' => 'Import Colors',
    ],

    'modal' => [
        'create_heading' => 'Create Property Value',
        'edit_heading' => 'Edit Property Value',
        'import_heading' => 'Import Color Values',
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

    'notifications' => [
        'import_queued' => [
            'title' => 'Import Queued',
            'body' => 'Color import has been queued and will be processed in the background.',
        ],
        'import_completed' => [
            'title' => 'Import Completed',
            'body' => 'Import completed: :inserted inserted, :skipped skipped, :errors errors.',
            'errors' => 'Errors',
        ],
    ],
];
