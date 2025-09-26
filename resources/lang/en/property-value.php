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
        'import_file' => 'Upload an Excel (.xlsx, .xls) or CSV file with two columns: <strong>name</strong> and <strong>hex</strong>. Example: Red, #FF0000',
    ],

    'table' => [
        'columns' => [
            'value' => 'Value',
            'group' => 'Group',
            'aliases' => 'Aliases',
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
            'group_aliases' => 'Group (aliases)',
            'remove_from_group' => 'Remove from group',
        ],
    ],

    'actions' => [
        'import' => 'Import Colors',
    ],

    'modal' => [
        'create_heading' => 'Create Property Value',
        'edit_heading' => 'Edit Property Value',
        'import_heading' => 'Import Color Values',
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

    'grouping' => [
        'success_grouped_title' => 'Grouped',
        'success_grouped_body' => ':count value(s) grouped under ":target".',
        'success_ungrouped_title' => 'Updated',
        'success_ungrouped_body' => ':count value(s) removed from group.',
        'error_title' => 'Grouping failed',
        'selected_values' => 'Selected values',
        'helper_target' => 'Choose the target value into which the selected values will be grouped.',
        'errors' => [
            'target_in_sources' => 'The target cannot be one of the selected values.',
            'target_is_member' => 'The selected target already belongs to another group.',
            'different_property' => 'All selected values and the target must belong to the same property.',
        ],
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

    'ui' => [
        'group_badge' => 'Group',
    ],

    'modal_grouping' => [
        'target_label' => 'Target value',
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
        'import_failed' => [
            'title' => 'Import Failed',
        ],
    ],
];
