<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Catalogue\Enums\ProductRelationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductRelation extends Model
{
    protected $table = 'pim_product_relations';

    protected $fillable = [
        'parent_id',
        'child_id',
        'type',
        'sort',
    ];

    protected $casts = [
        'type' => ProductRelationType::class,
        'sort' => 'integer',
    ];

    /**
     * Get the parent product.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Get the child product.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'child_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (ProductRelation $relation) {
            if ($relation->parent_id === $relation->child_id) {
                throw new \InvalidArgumentException('A product cannot be related to itself.');
            }
        });
    }

    /**
     * Scope to get relations by type.
     */
    public function scopeOfType($query, ProductRelationType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Scope to get relations for a specific parent product.
     */
    public function scopeForParent($query, int $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope to get relations ordered by sort.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort')->orderBy('id');
    }
}
