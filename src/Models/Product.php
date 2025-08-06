<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Factories\ProductFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, IsSearchable, SoftDeletes;

    protected $table = 'catalogue_products';

    protected $fillable = [
        'code',
        'barcode',
        'manufacturers_code',
        'suppliers_code',
        'net_weight',
        'gross_weight',
        'name',
        'category_id',
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
        'category_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->width(500)
            ->height(500)
            ->sharpen(10)
            ->nonQueued();
    }

    public function getCoverImageAttribute()
    {
        return $this->getMedia('images')->firstWhere('custom_properties.is_cover', true)
            ?? $this->getFirstMedia('images');
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
