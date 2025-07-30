<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\World\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
{
    use SoftDeletes;

    protected $table = 'pim_price_lists';

    protected $fillable = [
        'currency_id',
        'name',
        'code',
        'tax_included',
        'notes',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    protected function casts(): array
    {
        return [
            'tax_included' => 'boolean',
        ];
    }
}
