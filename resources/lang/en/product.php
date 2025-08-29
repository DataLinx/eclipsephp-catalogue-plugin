<?php

return [
    'singular' => 'Product',
    'plural' => 'Products',

    'fields' => [
        'product_type' => 'Product Type',
        'origin_country_id' => 'Country of Origin',
        'meta_title' => 'Meta Title',
        'meta_description' => 'Meta Description',
        'is_active' => 'Active',
        'has_free_delivery' => 'Free Delivery',
        'available_from_date' => 'Available From',
        'sorting_label' => 'Sorting Label',
        'category_id' => 'Category',
    ],

    'placeholders' => [
        'product_type' => 'Select product type (optional)',
        'origin_country_id' => 'Select country of origin',
        'meta_title' => 'SEO meta title',
        'meta_description' => 'SEO meta description',
        'category_id' => 'Select category (optional)',
    ],

    'table' => [
        'columns' => [
            'type' => 'Type',
            'is_active' => 'Active',
        ],
    ],

    'filters' => [
        'product_type' => 'Product Types',
    ],

    'sections' => [
        'tenant_settings' => 'Tenant Settings',
        'tenant_settings_description' => 'Configure product settings per tenant',
        'tenant_specific' => 'Tenant Specific Settings',
        'seo' => 'SEO',
        'seo_description' => 'Search engine optimization fields',
        'additional' => 'Additional Information',
    ],

    'help_text' => [
        'is_active' => 'Enable this product',
        'is_active_tenant' => 'Enable this product for :tenant',
        'has_free_delivery' => 'Mark product as free delivery',
        'has_free_delivery_tenant' => 'Mark product as free delivery for :tenant',
        'available_from_date' => 'Date/time when the product becomes available',
        'sorting_label' => 'Optional label used to influence sorting within lists',
    ],

    'price' => [
        'section' => 'Product price list',
        'list' => 'Price list',
        'fields' => [
            'price_list' => 'Price list',
            'price' => 'Price',
            'tax_included' => 'Incl. tax',
            'valid_from' => 'Valid from',
            'valid_to' => 'Valid to',
        ],
        'actions' => [
            'add' => 'Add price',
        ],
        'validation' => [
            'unique_title' => 'Duplicate price',
            'unique_body' => 'A price with the same Price List, Price and Valid From already exists in the form.',
        ],
    ],
];
