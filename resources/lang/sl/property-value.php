<?php

return [
    'singular' => 'Vrednost lastnosti',
    'plural' => 'Vrednosti lastnosti',

    'fields' => [
        'value' => 'Vrednost',
        'info_url' => 'URL informacij',
        'image' => 'Slika',
        'sort' => 'Vrstni red',
        'import_file' => 'Uvoz datoteke',
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
        'import_file' => 'Naložite Excel (.xlsx, .xls) ali CSV datoteko z dvema stolpcema: ime in hex. Primer: Rdeča, #FF0000',
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

    'actions' => [
        'import' => 'Uvozi barve',
    ],

    'modal' => [
        'create_heading' => 'Ustvari vrednost lastnosti',
        'edit_heading' => 'Uredi vrednost lastnosti',
        'import_heading' => 'Uvozi vrednosti barv',
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

    'notifications' => [
        'import_queued' => [
            'title' => 'Uvoz v čakalni vrsti',
            'body' => 'Uvoz barv je bil dodan v čakalno vrsto in bo obdelan v ozadju.',
        ],
        'import_completed' => [
            'title' => 'Uvoz končan',
            'body' => 'Uvoz končan: :inserted dodano, :skipped preskočeno, :errors napak.',
            'errors' => 'Napake',
        ],
    ],
];
