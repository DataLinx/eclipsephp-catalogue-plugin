<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy config
    |--------------------------------------------------------------------------
    */
    'tenancy' => [
        'model' => Eclipse\Core\Models\Site::class,
        'foreign_key' => 'site_id',
    ],
];
