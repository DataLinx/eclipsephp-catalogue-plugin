<?php

namespace Eclipse\Catalogue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductData extends Model
{
    use HasFactory;

    protected $table = 'catalogue_product_data';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'sorting_label',
        'is_active',
        'available_from_date',
        'has_free_delivery',
    ];

    /**
     * Include the tenant foreign key in fillable when tenancy is on.
     */
    public function getFillable(): array
    {
        $fillable = $this->fillable;

        if (config('eclipse-catalogue.tenancy.foreign_key')) {
            $fillable[] = config('eclipse-catalogue.tenancy.foreign_key');
        }

        return $fillable;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<\Eclipse\Core\Models\Site, self> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(\Eclipse\Core\Models\Site::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'available_from_date' => 'datetime',
            'has_free_delivery' => 'boolean',
        ];
    }

    protected static function newFactory()
    {
        return \Eclipse\Catalogue\Factories\ProductDataFactory::new();
    }
}
