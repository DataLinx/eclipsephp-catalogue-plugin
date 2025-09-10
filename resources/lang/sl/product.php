<?php

return [
    'singular' => 'Izdelek',
    'plural' => 'Izdelki',

    'fields' => [
        'product_type' => 'Tip proizvoda',
        'origin_country_id' => 'Država izvora',
        'meta_title' => 'Meta naslov',
        'meta_description' => 'Meta opis',
        'is_active' => 'Aktiven',
        'has_free_delivery' => 'Brezplačna dostava',
        'available_from_date' => 'Na voljo od',
        'sorting_label' => 'Oznaka za razvrščanje',
        'category_id' => 'Kategorija',
    ],

    'placeholders' => [
        'product_type' => 'Izberi tip proizvoda (neobvezno)',
        'origin_country_id' => 'Izberi državo izvora',
        'meta_title' => 'SEO meta naslov',
        'meta_description' => 'SEO meta opis',
        'category_id' => 'Izberi kategorijo (neobvezno)',
    ],

    'table' => [
        'columns' => [
            'type' => 'Tip',
            'is_active' => 'Aktiven',
        ],
    ],

    'filters' => [
        'product_type' => 'Tipi proizvodov',
    ],

    'sections' => [
        'tenant_settings' => 'Nastavitve najemnikov',
        'tenant_settings_description' => 'Nastavi parametre izdelka za posamezne najemnike',
        'tenant_specific' => 'Nastavitve za najemnika',
        'seo' => 'SEO',
        'seo_description' => 'Polja za optimizacijo iskalnikov',
        'additional' => 'Dodatne informacije',
    ],

    'help_text' => [
        'is_active' => 'Omogoči ta izdelek',
        'is_active_tenant' => 'Omogoči ta izdelek za :tenant',
        'has_free_delivery' => 'Označi izdelek kot brezplačna dostava',
        'has_free_delivery_tenant' => 'Označi izdelek kot brezplačna dostava za :tenant',
        'available_from_date' => 'Datum/čas, ko bo izdelek na voljo',
        'sorting_label' => 'Neobvezna oznaka, ki vpliva na razvrščanje v seznamih',
    ],

    'price' => [
        'tab' => 'Cene',
        'section' => 'Cenik izdelka',
        'list' => 'Cenik',
        'fields' => [
            'price_list' => 'Cenik',
            'price' => 'Cena',
            'tax_included' => 'Z DDV',
            'valid_from' => 'Velja od',
            'valid_to' => 'Velja do',
        ],
        'actions' => [
            'add' => 'Dodaj ceno',
        ],
        'validation' => [
            'unique_title' => 'Podvojen vnos cene',
            'unique_body' => 'Cena za ta cenik z istim datumom "Velja od" že obstaja.',
        ],
    ],
];
