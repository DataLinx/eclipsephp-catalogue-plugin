<?php

return [
    'singular' => 'Lastnost',
    'plural' => 'Lastnosti',

    'fields' => [
        'name' => 'Ime',
        'code' => 'Koda',
        'description' => 'Opis',
        'internal_name' => 'Interno ime',
        'is_active' => 'Aktiven',
        'is_global' => 'Globalna lastnost',
        'max_values' => 'Največje število vrednosti',
        'enable_sorting' => 'Omogoči ročno razvrščanje',
        'is_filter' => 'Prikaži kot filter',
        'product_types' => 'Dodeli tipom proizvodov',
    ],

    'sections' => [
        'basic_information' => 'Osnovne informacije',
        'configuration' => 'Konfiguracija',
        'product_types' => 'Tipi proizvodov',
    ],

    'placeholders' => [
        'name' => 'Vnesite ime lastnosti',
        'code' => 'Neobvezna alfanumerična koda s podčrtaji',
        'description' => 'Vnesite opis lastnosti',
        'internal_name' => 'Vnesite interno ime za razlikovanje',
    ],

    'help_text' => [
        'code' => 'Neobvezna alfanumerična koda s podčrtaji, avtomatsko pretvorjena v male črke',
        'internal_name' => 'Interno ime za razlikovanje, ni prevedeno',
        'is_global' => 'Avtomatsko dodeljeno vsem tipom proizvodov',
        'max_values' => 'Največje število vrednosti, ki so dovoljene za to lastnost (1 = ena vrednost, 2+ = več vrednosti)',
        'enable_sorting' => 'Dovoli razvrščanje vrednosti lastnosti z vlečenjem',
        'is_filter' => 'Prikaži lastnost kot filter v tabeli proizvodov',
        'product_types' => 'Izberi tipe proizvodov za to lastnost (ignorirano, če je Global omogočeno)',
    ],

    'table' => [
        'columns' => [
            'code' => 'Koda',
            'name' => 'Ime',
            'internal_name' => 'Interno ime',
            'is_global' => 'Globalna',
            'max_values' => 'Največ vrednosti',
            'enable_sorting' => 'Razvrščanje',
            'is_filter' => 'Filter',
            'is_active' => 'Aktiven',
            'values_count' => 'Vrednosti',
            'created_at' => 'Ustvarjeno',
        ],
        'filters' => [
            'product_type' => 'Tip proizvoda',
            'is_global' => 'Globalne lastnosti',
            'is_active' => 'Aktivne lastnosti',
            'is_filter' => 'Lastnosti filtra',
        ],
        'actions' => [
            'values' => 'Vrednosti',
            'edit' => 'Uredi',
            'delete' => 'Izbriši',
        ],
    ],

    'messages' => [
        'created' => 'Lastnost je bila uspešno ustvarjena.',
        'updated' => 'Lastnost je bila uspešno posodobljena.',
        'deleted' => 'Lastnost je bila uspešno izbrisana.',
    ],
];
