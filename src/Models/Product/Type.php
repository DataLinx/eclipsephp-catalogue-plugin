<?php

namespace Eclipse\Catalogue\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use SoftDeletes;

    protected $table = 'pim_product_types';

    protected $fillable = [
        'name',
        'code',
    ];
}
