<?php

use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Product;

beforeEach(function () {
    $this->migrate();
});

it('can update product sort order in a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $product = Product::create([
        'name' => 'Test Product',
        'code' => 'TEST-001',
    ]);

    $group->addProduct($product, 1);
    $group->updateProductSort($product, 5);

    $groupProduct = $group->products()->first();
    expect($groupProduct->pivot->sort)->toBe(5);
});

it('can get next sort order for a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    // Empty group should return 1
    expect($group->getNextSortOrder())->toBe(1);

    $product1 = Product::create(['name' => 'Product 1', 'code' => 'TEST-001']);
    $product2 = Product::create(['name' => 'Product 2', 'code' => 'TEST-002']);

    $group->addProduct($product1, 10);
    $group->addProduct($product2, 20);

    // Next sort order should be 21
    expect($group->getNextSortOrder())->toBe(21);
});

it('can reorder products in a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $products = [
        Product::create(['name' => 'Product 1', 'code' => 'TEST-001']),
        Product::create(['name' => 'Product 2', 'code' => 'TEST-002']),
        Product::create(['name' => 'Product 3', 'code' => 'TEST-003']),
    ];

    // Add products with initial sort order
    $group->addProduct($products[0], 1);
    $group->addProduct($products[1], 2);
    $group->addProduct($products[2], 3);

    // Reorder products (reverse order)
    $group->reorderProducts([$products[2]->id, $products[1]->id, $products[0]->id]);

    $groupProducts = $group->products()->get();

    expect($groupProducts)->toHaveCount(3);
    expect($groupProducts[0]->id)->toBe($products[2]->id);
    expect($groupProducts[0]->pivot->sort)->toBe(1);
    expect($groupProducts[1]->id)->toBe($products[1]->id);
    expect($groupProducts[1]->pivot->sort)->toBe(2);
    expect($groupProducts[2]->id)->toBe($products[0]->id);
    expect($groupProducts[2]->pivot->sort)->toBe(3);
});

it('maintains sort order when adding new products', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $product1 = Product::create(['name' => 'Product 1', 'code' => 'TEST-001']);
    $product2 = Product::create(['name' => 'Product 2', 'code' => 'TEST-002']);

    // Add products with specific sort order
    $group->addProduct($product1, 10);
    $group->addProduct($product2, 20);

    $groupProducts = $group->products()->get();

    expect($groupProducts)->toHaveCount(2);
    expect($groupProducts[0]->pivot->sort)->toBe(10);
    expect($groupProducts[1]->pivot->sort)->toBe(20);

    // Add another product without specifying sort (should use next available)
    $product3 = Product::create(['name' => 'Product 3', 'code' => 'TEST-003']);
    $group->addProduct($product3);

    $groupProducts = $group->products()->get();
    expect($groupProducts)->toHaveCount(3);
    expect($groupProducts[2]->pivot->sort)->toBe(21); // Next sort order
});

it('can handle reordering with gaps in sort values', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $products = [
        Product::create(['name' => 'Product 1', 'code' => 'TEST-001']),
        Product::create(['name' => 'Product 2', 'code' => 'TEST-002']),
        Product::create(['name' => 'Product 3', 'code' => 'TEST-003']),
    ];

    // Add products with gaps in sort order
    $group->addProduct($products[0], 10);
    $group->addProduct($products[1], 50);
    $group->addProduct($products[2], 100);

    // Reorder products
    $group->reorderProducts([$products[1]->id, $products[0]->id, $products[2]->id]);

    $groupProducts = $group->products()->get();

    expect($groupProducts)->toHaveCount(3);
    expect($groupProducts[0]->id)->toBe($products[1]->id);
    expect($groupProducts[0]->pivot->sort)->toBe(1);
    expect($groupProducts[1]->id)->toBe($products[0]->id);
    expect($groupProducts[1]->pivot->sort)->toBe(2);
    expect($groupProducts[2]->id)->toBe($products[2]->id);
    expect($groupProducts[2]->pivot->sort)->toBe(3);
});

it('products are ordered by sort value when retrieved', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $products = [
        Product::create(['name' => 'Product 1', 'code' => 'TEST-001']),
        Product::create(['name' => 'Product 2', 'code' => 'TEST-002']),
        Product::create(['name' => 'Product 3', 'code' => 'TEST-003']),
    ];

    // Add products in random order
    $group->addProduct($products[2], 3);
    $group->addProduct($products[0], 1);
    $group->addProduct($products[1], 2);

    $groupProducts = $group->products()->get();

    expect($groupProducts)->toHaveCount(3);
    expect($groupProducts[0]->id)->toBe($products[0]->id); // Sort 1
    expect($groupProducts[1]->id)->toBe($products[1]->id); // Sort 2
    expect($groupProducts[2]->id)->toBe($products[2]->id); // Sort 3
});
