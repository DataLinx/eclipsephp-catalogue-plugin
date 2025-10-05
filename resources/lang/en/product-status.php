<?php

return [
    'singular' => 'Product Status',
    'plural' => 'Product Statuses',
    'navigation_label' => 'Product Statuses',

    'fields' => [
        'code' => 'Code',
        'title' => 'Title',
        'description' => 'Description',
        'label_type' => 'Label Type',
        'shown_in_browse' => 'Shown in browse',
        'allow_price_display' => 'Allow price display',
        'allow_sale' => 'Allow sale',
        'is_default' => 'Default status',
        'priority' => 'Priority',
        'sd_item_availability' => 'Item availability',
        'skip_stock_qty_check' => 'Skip stock quantity check',
        'no_status' => 'No status',
    ],

    'help_text' => [
        'code' => 'Unique identifier for this status',
        'title' => 'Display name for this status',
        'description' => 'Optional description of this status',
        'label_type' => 'Color theme for displaying this status as a badge',
        'shown_in_browse' => 'Whether products with this status appear in catalog browsing',
        'allow_price_display' => 'Whether to show prices for products with this status',
        'allow_sale' => 'Whether products with this status can be purchased (automatically disabled if price display is off)',
        'is_default' => 'Set as the default status for new products',
        'priority' => 'Status priority — lower number is better. Used for automatically deciding between e.g. supplier offers or product variants.',
        'sd_item_availability' => 'Structured data item availability value <a href="https://schema.org/ItemAvailability" target="_blank" rel="noopener">(more info)</a>',
        'skip_stock_qty_check' => 'When ordering is possible, do not check if there is available stock',
    ],

    'sections' => [
        'visibility_rules' => 'Visibility & Rules',
    ],

    'actions' => [
        'create' => 'New product status',
    ],

    'validation' => [
        'code_unique' => 'This code is already in use.',
    ],
];
