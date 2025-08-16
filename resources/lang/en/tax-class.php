<?php

return [
    'singular' => 'Tax class',
    'plural' => 'Tax classes',
    'fields' => [
        'name' => 'Name',
        'description' => 'Description',
        'rate' => 'Rate (%)',
        'is_default' => 'Default class',
    ],
    'messages' => [
        'default_class_help' => 'Only one class can be set as default',
        'cannot_delete_default' => 'Cannot delete the default tax class.',
    ],
];
