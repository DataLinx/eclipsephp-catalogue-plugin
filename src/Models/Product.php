<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\ProductFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Eclipse\World\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'sort',
        'is_active',
        'stock_qty',
        'min_stock_qty',
        'stock_date',
        'available_from_date',
        'free_delivery',
        'origin_country_id',
        'meta_desc',
        'meta_title',
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description',
        'meta_desc',
        'meta_title',
    ];

    protected $casts = [
        'name' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'meta_desc' => 'array',
        'meta_title' => 'array',
        'is_active' => 'boolean',
        'free_delivery' => 'boolean',
        'stock_qty' => 'decimal:5',
        'min_stock_qty' => 'decimal:5',
        'stock_date' => 'date',
        'available_from_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function inStock(): bool
    {
        return $this->stock_qty > 0;
    }

    public function lowStock(): bool
    {
        return $this->stock_qty <= $this->min_stock_qty;
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->available_from_date && $this->available_from_date->isFuture()) {
            return false;
        }

        return true;
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_qty', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'min_stock_qty');
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
                        'name' => 'barcode',
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
                        'name' => 'short_description_.*',
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
                    'code',
                    'barcode',
                    'name_*',
                    'short_description_*',
                    'description_*',
                ]),
            ],
        ];
    }
}
