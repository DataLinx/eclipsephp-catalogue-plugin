<?php

return [
    'singular' => 'Davčni razred',
    'plural' => 'Davčni razredi',
    'fields' => [
        'name' => 'Ime',
        'description' => 'Opis',
        'rate' => 'Stopnja (%)',
        'is_default' => 'Privzeti razred',
    ],
    'messages' => [
        'default_class_help' => 'Samo en razred je lahko nastavljen kot privzet',
        'cannot_delete_default' => 'Privzetega davčnega razreda ni mogoče izbrisati.',
    ],
];
