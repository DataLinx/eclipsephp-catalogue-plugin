<?php

use Eclipse\Catalogue\Enums\ProductRelationType;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductRelation;

beforeEach(function () {
    $this->migrate();
    $this->setUpSuperAdminAndTenant();
});

it('can create product relations', function () {
    $parent = Product::factory()->create();
    $child = Product::factory()->create();

    $relation = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    expect($relation->exists)->toBeTrue();
    expect($relation->type)->toBe(ProductRelationType::RELATED);
    expect($relation->parent_id)->toBe($parent->id);
    expect($relation->child_id)->toBe($child->id);
});

it('prevents self-relations', function () {
    $product = Product::factory()->create();

    expect(function () use ($product) {
        ProductRelation::create([
            'parent_id' => $product->id,
            'child_id' => $product->id,
            'type' => ProductRelationType::RELATED,
        ]);
    })->toThrow(\InvalidArgumentException::class, 'A product cannot be related to itself.');
});

it('enforces unique constraint', function () {
    $parent = Product::factory()->create();
    $child = Product::factory()->create();

    // Create first relation
    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    // Attempt to create duplicate relation
    expect(function () use ($parent, $child) {
        ProductRelation::create([
            'parent_id' => $parent->id,
            'child_id' => $child->id,
            'type' => ProductRelationType::RELATED,
            'sort' => 2,
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

it('allows same products with different relation types', function () {
    $parent = Product::factory()->create();
    $child = Product::factory()->create();

    $relatedRelation = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    $crossSellRelation = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::CROSS_SELL,
        'sort' => 1,
    ]);

    expect($relatedRelation->exists)->toBeTrue();
    expect($crossSellRelation->exists)->toBeTrue();
});

it('has proper relationships', function () {
    $parent = Product::factory()->create();
    $child = Product::factory()->create();

    $relation = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::UPSELL,
    ]);

    expect($relation->parent->id)->toBe($parent->id);
    expect($relation->child->id)->toBe($child->id);
});

it('can scope by type', function () {
    $parent = Product::factory()->create();
    $child1 = Product::factory()->create();
    $child2 = Product::factory()->create();

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child1->id,
        'type' => ProductRelationType::RELATED,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child2->id,
        'type' => ProductRelationType::CROSS_SELL,
    ]);

    $relatedRelations = ProductRelation::ofType(ProductRelationType::RELATED)->get();
    $crossSellRelations = ProductRelation::ofType(ProductRelationType::CROSS_SELL)->get();

    expect($relatedRelations->count())->toBe(1);
    expect($crossSellRelations->count())->toBe(1);
    expect($relatedRelations->first()->child_id)->toBe($child1->id);
    expect($crossSellRelations->first()->child_id)->toBe($child2->id);
});

it('can scope by parent', function () {
    $parent1 = Product::factory()->create();
    $parent2 = Product::factory()->create();
    $child = Product::factory()->create();

    ProductRelation::create([
        'parent_id' => $parent1->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::RELATED,
    ]);

    ProductRelation::create([
        'parent_id' => $parent2->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::RELATED,
    ]);

    $parent1Relations = ProductRelation::forParent($parent1->id)->get();
    $parent2Relations = ProductRelation::forParent($parent2->id)->get();

    expect($parent1Relations->count())->toBe(1);
    expect($parent2Relations->count())->toBe(1);
});

it('orders by sort and id', function () {
    $parent = Product::factory()->create();
    $child1 = Product::factory()->create();
    $child2 = Product::factory()->create();
    $child3 = Product::factory()->create();

    // Create in random order
    $relation2 = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child2->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 2,
    ]);

    $relation1 = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child1->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    $relation3 = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child3->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    $orderedRelations = ProductRelation::forParent($parent->id)->ordered()->get();

    expect($orderedRelations->count())->toBe(3);
    // First should be the one with sort=1 and lower ID
    expect($orderedRelations->first()->id)->toBe($relation1->id);
    // Last should be the one with sort=2
    expect($orderedRelations->last()->id)->toBe($relation2->id);
});
