<?php

use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Product;

beforeEach(function () {
    $this->migrate();
});

it('can bulk add products to a group', function () {
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

    // Bulk add products
    foreach ($products as $product) {
        $group->addProduct($product);
    }

    expect($group->products)->toHaveCount(3);
    expect($group->products_count)->toBe(3);

    foreach ($products as $product) {
        expect($group->hasProduct($product))->toBeTrue();
    }
});

it('can bulk remove products from a group', function () {
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

    // Add products first
    foreach ($products as $product) {
        $group->addProduct($product);
    }

    expect($group->products)->toHaveCount(3);

    // Bulk remove products
    foreach ($products as $product) {
        $group->removeProduct($product);
    }

    // Refresh the group to get updated products count
    $group->refresh();
    expect($group->products)->toHaveCount(0);
    expect($group->products_count)->toBe(0);

    foreach ($products as $product) {
        expect($group->hasProduct($product))->toBeFalse();
    }
});

it('can bulk add products with custom sort order', function () {
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

    // Add products with custom sort order
    $group->addProduct($products[0], 10);
    $group->addProduct($products[1], 20);
    $group->addProduct($products[2], 30);

    $groupProducts = $group->products()->get();

    expect($groupProducts)->toHaveCount(3);
    expect($groupProducts[0]->pivot->sort)->toBe(10);
    expect($groupProducts[1]->pivot->sort)->toBe(20);
    expect($groupProducts[2]->pivot->sort)->toBe(30);
});

it('can handle bulk operations on empty group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $product = Product::create(['name' => 'Product 1', 'code' => 'TEST-001']);

    expect($group->products)->toHaveCount(0);
    expect($group->products_count)->toBe(0);

    // Add product to empty group
    $group->addProduct($product);

    // Refresh to get updated products
    $group->refresh();
    expect($group->products)->toHaveCount(1);
    expect($group->hasProduct($product))->toBeTrue();

    // Remove product from group
    $group->removeProduct($product);

    // Refresh to get updated products
    $group->refresh();
    expect($group->products)->toHaveCount(0);
    expect($group->hasProduct($product))->toBeFalse();
});

it('can get products count attribute', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    expect($group->products_count)->toBe(0);

    $product1 = Product::create(['name' => 'Product 1', 'code' => 'TEST-001']);
    $product2 = Product::create(['name' => 'Product 2', 'code' => 'TEST-002']);

    $group->addProduct($product1);
    expect($group->products_count)->toBe(1);

    $group->addProduct($product2);
    expect($group->products_count)->toBe(2);

    $group->removeProduct($product1);
    expect($group->products_count)->toBe(1);
});
