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
            'merge' => 'Merge…',
        ],
    ],

    'modal' => [
        'create_heading' => 'Create Property Value',
        'edit_heading' => 'Edit Property Value',
        'merge_heading' => 'Merge Value',
        'merge_from_label' => 'Merge value…',
        'merge_to_label' => 'With value…*',
        'merge_helper' => 'This will replace the value on all products with the selected one above, then delete the current value.',
        'merge_submit_label' => 'Merge',
        'cancel_label' => 'Cancel',
        'merge_confirm_title' => 'Are you sure you want to merge?',
        'merge_confirm_body' => 'All products using the current value will be updated to the selected value. This action cannot be undone.',
    ],

    'messages' => [
        'created' => 'Property value created successfully.',
        'updated' => 'Property value updated successfully.',
        'deleted' => 'Property value deleted successfully.',
        'merged_title' => 'Values merged',
        'merged_body' => ':affected product(s) updated. The selected value was kept and the other was removed.',
        'merged_error_title' => 'Merge failed',
        'merged_error_body' => 'We couldn’t merge these values. Please try again.',
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
