<?php

namespace Eclipse\Catalogue\Models\Product;

use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $table = 'catalogue_product_prices';

    protected $fillable = [
        'id',
        'product_id',
        'price_list_id',
        'valid_from',
        'valid_to',
        'price',
        'tax_included',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'tax_included' => 'boolean',
        ];
    }
}
