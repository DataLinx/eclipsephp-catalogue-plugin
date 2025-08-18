<?php

return [
    'singular' => 'Tip proizvoda',
    'plural' => 'Tipi proizvodov',

    'fields' => [
        'name' => 'Ime',
        'code' => 'Koda',
        'is_active' => 'Aktiven',
        'is_default' => 'Privzeti tip',
        'tenant' => 'Najemnik',
    ],

    'sections' => [
        'information' => 'Informacije o tipu proizvoda',
        'information_description' => 'Osnovne informacije o tipu proizvoda',
        'settings' => 'Nastavitve',
        'settings_description' => 'Konfiguriraj obnašanje tipa proizvoda',
        'tenant_settings' => 'Nastavitve najemnika',
        'tenant_settings_description' => 'Konfiguriraj nastavitve tipa proizvoda za vsakega najemnika/spletno mesto',
        'default_settings' => 'Privzete nastavitve',
    ],

    'placeholders' => [
        'name' => 'Vnesite ime tipa proizvoda',
        'code' => 'Neobvezna koda za identifikacijo',
        'tenant' => 'Izberi najemnika',
    ],

    'help_text' => [
        'is_active' => 'Omogoči ta tip proizvoda',
        'is_active_tenant' => 'Omogoči ta tip proizvoda za :tenant',
        'is_default' => 'Uporabi kot privzeti tip proizvoda',
        'is_default_tenant' => 'Uporabi kot privzeti tip proizvoda za :tenant',
    ],

    'table' => [
        'columns' => [
            'id' => 'ID',
            'name' => 'Ime',
            'code' => 'Koda',
            'is_active' => 'Aktiven',
            'is_default' => 'Privzeti tip',
            'created_at' => 'Datum nastanka',
            'updated_at' => 'Datum zadnje spremembe',
        ],
    ],

    'notifications' => [
        'conflict_resolved_title' => 'Konflikt rešen',
        'conflict_resolved_is_default_disabled' => 'Samo en tip proizvoda je lahko privzet za :tenant. Prejšnji privzeti je bil onemogočen.',
    ],

    'validation' => [
        'only_one_default_per_tenant' => 'Samo en tip proizvoda je lahko nastavljen kot privzet na najemnika.',
    ],

    'messages' => [
        'default_help' => 'Samo en tip proizvoda je lahko nastavljen kot privzet na najemnika.',
        'cannot_delete_default' => 'Privzetega tipa proizvoda ni mogoče izbrisati.',
    ],

    'labels' => [
        'current' => 'Trenutni',
        'tenant_switcher' => 'Preklopnik najemnika',
    ],
];
