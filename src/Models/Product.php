<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\ProductFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations, IsSearchable, SoftDeletes;

    protected $table = 'catalogue_products';

    protected $fillable = [
        'code',
        'barcode',
        'manufacturers_code',
        'suppliers_code',
        'net_weight',
        'gross_weight',
        'name',
        'short_description',
        'description',
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public static function getTypesenseSettings(): array
    {
        return [
            'collection-schema' => [
                'fields' => [
                    [
                        'name' => 'id',
                        'type' => 'string',
                    ],
                    [
                        'name' => 'code',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'created_at',
                        'type' => 'int64',
                    ],
                    [
                        'name' => 'name_.*',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'sef_key_.*',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'short_desc_.*',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => 'description_.*',
                        'type' => 'string',
                        'optional' => true,
                    ],
                    [
                        'name' => '__soft_deleted',
                        'type' => 'int32',
                        'optional' => true,
                    ],
                ],
            ],
            'search-parameters' => [
                'query_by' => implode(', ', [
                    'name_*',
                    'sef_key_*',
                    'short_desc_*',
                    'description_*',
                    'code',
                ]),
            ],
        ];
    }
}
