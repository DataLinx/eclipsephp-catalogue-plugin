<?php

return [
    'singular' => 'Vrednost lastnosti',
    'plural' => 'Vrednosti lastnosti',

    'fields' => [
        'value' => 'Vrednost',
        'info_url' => 'URL informacij',
        'image' => 'Slika',
        'sort' => 'Vrstni red',
    ],

    'sections' => [
        'value_information' => 'Informacije o vrednosti',
    ],

    'placeholders' => [
        'value' => 'Vnesite vrednost lastnosti',
        'info_url' => 'Vnesite neobvezno povezavo "več informacij"',
        'sort' => 'Vnesite vrstni red (nižje številke se prikažejo prve)',
    ],

    'help_text' => [
        'info_url' => 'Neobvezna povezava "več informacij"',
        'image' => 'Neobvezna slika za to vrednost (npr. logotip blagovne znamke)',
        'sort' => 'Nižje številke se prikažejo prve',
    ],

    'table' => [
        'columns' => [
            'value' => 'Vrednost',
            'image' => 'Slika',
            'info_url' => 'URL informacij',
            'sort' => 'Vrstni red',
            'products_count' => 'Proizvodi',
            'created_at' => 'Ustvarjeno',
        ],
        'filters' => [
            'property' => 'Lastnost',
        ],
        'actions' => [
            'edit' => 'Uredi',
            'delete' => 'Izbriši',
        ],
    ],

    'modal' => [
        'create_heading' => 'Ustvari vrednost lastnosti',
        'edit_heading' => 'Uredi vrednost lastnosti',
    ],

    'messages' => [
        'created' => 'Vrednost lastnosti je bila uspešno ustvarjena.',
        'updated' => 'Vrednost lastnosti je bila uspešno posodobljena.',
        'deleted' => 'Vrednost lastnosti je bila uspešno izbrisana.',
    ],

    'pages' => [
        'title' => [
            'with_property' => 'Vrednosti za: :property',
            'default' => 'Vrednosti lastnosti',
        ],
        'breadcrumbs' => [
            'properties' => 'Lastnosti',
            'list' => 'Seznam',
        ],
    ],
];
