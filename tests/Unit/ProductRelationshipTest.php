<?php

use Eclipse\Catalogue\Enums\ProductRelationType;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductRelation;

beforeEach(function () {
    $this->migrate();
    $this->setUpSuperAdminAndTenant();
});

it('has related products relationship', function () {
    $parent = Product::factory()->create();
    $child1 = Product::factory()->create();
    $child2 = Product::factory()->create();

    // Create related products
    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child1->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child2->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 2,
    ]);

    // Create non-related product (cross-sell)
    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => Product::factory()->create()->id,
        'type' => ProductRelationType::CROSS_SELL,
        'sort' => 1,
    ]);

    $relatedProducts = $parent->related;

    expect($relatedProducts->count())->toBe(2);
    expect($relatedProducts->pluck('id')->toArray())->toEqual([$child1->id, $child2->id]);
});

it('has cross-sell products relationship', function () {
    $parent = Product::factory()->create();
    $child1 = Product::factory()->create();
    $child2 = Product::factory()->create();

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child1->id,
        'type' => ProductRelationType::CROSS_SELL,
        'sort' => 2,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child2->id,
        'type' => ProductRelationType::CROSS_SELL,
        'sort' => 1,
    ]);

    $crossSellProducts = $parent->crossSell;

    expect($crossSellProducts->count())->toBe(2);
    // Should be ordered by sort
    expect($crossSellProducts->first()->id)->toBe($child2->id);
    expect($crossSellProducts->last()->id)->toBe($child1->id);
});

it('has upsell products relationship', function () {
    $parent = Product::factory()->create();
    $child = Product::factory()->create();

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::UPSELL,
        'sort' => 1,
    ]);

    $upsellProducts = $parent->upsell;

    expect($upsellProducts->count())->toBe(1);
    expect($upsellProducts->first()->id)->toBe($child->id);
});

it('has relations relationship', function () {
    $parent = Product::factory()->create();
    $child1 = Product::factory()->create();
    $child2 = Product::factory()->create();

    $relation1 = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child1->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    $relation2 = ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child2->id,
        'type' => ProductRelationType::CROSS_SELL,
        'sort' => 1,
    ]);

    $allRelations = $parent->relations;

    expect($allRelations->count())->toBe(2);
    expect($allRelations->pluck('id')->toArray())->toContain($relation1->id, $relation2->id);
});

it('orders relationships by sort and id', function () {
    $parent = Product::factory()->create();
    $child1 = Product::factory()->create();
    $child2 = Product::factory()->create();
    $child3 = Product::factory()->create();

    // Create with different sort orders
    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child2->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 3,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child1->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 1,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child3->id,
        'type' => ProductRelationType::RELATED,
        'sort' => 2,
    ]);

    $relatedProducts = $parent->related;

    expect($relatedProducts->count())->toBe(3);
    expect($relatedProducts->pluck('id')->toArray())->toEqual([
        $child1->id, // sort: 1
        $child3->id, // sort: 2
        $child2->id, // sort: 3
    ]);
});

it('relationships are separate by type', function () {
    $parent = Product::factory()->create();
    $child = Product::factory()->create();

    // Create all three types of relations with the same child
    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::RELATED,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::CROSS_SELL,
    ]);

    ProductRelation::create([
        'parent_id' => $parent->id,
        'child_id' => $child->id,
        'type' => ProductRelationType::UPSELL,
    ]);

    expect($parent->related->count())->toBe(1);
    expect($parent->crossSell->count())->toBe(1);
    expect($parent->upsell->count())->toBe(1);
    expect($parent->relations->count())->toBe(3);
});
