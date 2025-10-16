<?php

namespace Eclipse\Catalogue\Services;

use Eclipse\Catalogue\Models\ProductRelation;

/**
 * Service for managing product relations.
 *
 * Used by ProductSelectorTable to add selected products to product relations
 * when users select products in the modal and click "Add selected products".
 */
class ProductRelationService
{
    public static function addBuffered(int $parentId, string $type, array $ids): void
    {
        $next = (int) (ProductRelation::query()
            ->where('parent_id', $parentId)
            ->where('type', $type)
            ->max('sort') ?? 0) + 1;

        foreach ($ids as $id) {
            if (! is_numeric($id)) {
                continue;
            }
            ProductRelation::firstOrCreate([
                'parent_id' => $parentId,
                'child_id' => (int) $id,
                'type' => $type,
            ], [
                'sort' => $next++,
            ]);
        }
    }
}
