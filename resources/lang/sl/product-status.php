<?php

return [
    'singular' => 'Status izdelka',
    'plural' => 'Statusi izdelka',
    'navigation_label' => 'Statusi izdelka',

    'fields' => [
        'code' => 'Koda',
        'title' => 'Naziv',
        'description' => 'Opis',
        'label_type' => 'Vrsta oznake',
        'shown_in_browse' => 'Prikaži v katalogu',
        'allow_price_display' => 'Prikaži ceno',
        'allow_sale' => 'Dovoli prodajo',
        'is_default' => 'Privzeti status',
        'priority' => 'Prioriteta',
        'sd_item_availability' => 'Razpoložljivost',
        'skip_stock_qty_check' => 'Ne preverjaj zaloge',
        'no_status' => 'Brez statusa',
    ],

    'help_text' => [
        'code' => 'Edinstveni identifikator za ta status',
        'title' => 'Prikazno ime za ta status',
        'description' => 'Opcijski opis tega statusa',
        'label_type' => 'Barvna tema za prikaz tega statusa kot oznake',
        'shown_in_browse' => 'Ali se izdelki s tem statusom prikažejo pri brskanju po katalogu',
        'allow_price_display' => 'Ali prikazati cene za izdelke s tem statusom',
        'allow_sale' => 'Ali se izdelki s tem statusom lahko kupijo (samodejno onemogočeno, če je prikaz cene izklopljen)',
        'is_default' => 'Nastavi kot privzeti status za nove izdelke',
        'priority' => 'Prioriteta statusa — manjše število je boljše. Uporabljeno pri samodejnem odločanju pri npr. primerjavi ponudbi dobaviteljev ali variant izdelka.',
        'sd_item_availability' => 'Strukturirana vrednost razpoložljivosti izdelka <a href="https://schema.org/ItemAvailability" target="_blank" rel="noopener">(več informacij)</a>',
        'skip_stock_qty_check' => 'Ko je naročanje omogočeno, ne preverjaj razpoložljivosti zaloge',
    ],

    'sections' => [
        'visibility_rules' => 'Vidnost in pravila',
    ],

    'actions' => [
        'create' => 'Nov status izdelka',
    ],

    'validation' => [
        'code_unique' => 'Ta koda je že v uporabi.',
    ],
];
