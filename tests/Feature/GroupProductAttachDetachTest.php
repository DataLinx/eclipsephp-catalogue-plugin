<?php

use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Product;

beforeEach(function () {
    $this->migrate();
});

it('can attach a product to a group', function () {
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

    $group->addProduct($product);

    expect($group->hasProduct($product))->toBeTrue();
    expect($group->products)->toHaveCount(1);
    expect($group->products->first()->id)->toBe($product->id);

    $this->assertDatabaseHas('pim_group_has_product', [
        'group_id' => $group->id,
        'product_id' => $product->id,
    ]);
});

it('can detach a product from a group', function () {
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

    $group->addProduct($product);
    expect($group->hasProduct($product))->toBeTrue();

    $group->removeProduct($product);

    expect($group->hasProduct($product))->toBeFalse();
    expect($group->products)->toHaveCount(0);

    $this->assertDatabaseMissing('pim_group_has_product', [
        'group_id' => $group->id,
        'product_id' => $product->id,
    ]);
});

it('prevents duplicate product attachments', function () {
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

    $group->addProduct($product);
    $group->addProduct($product); // Try to add same product again

    expect($group->products)->toHaveCount(1);
    expect($group->hasProduct($product))->toBeTrue();
});

it('can attach multiple products to a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $product1 = Product::create([
        'name' => 'Product 1',
        'code' => 'TEST-001',
    ]);

    $product2 = Product::create([
        'name' => 'Product 2',
        'code' => 'TEST-002',
    ]);

    $product3 = Product::create([
        'name' => 'Product 3',
        'code' => 'TEST-003',
    ]);

    $group->addProduct($product1);
    $group->addProduct($product2);
    $group->addProduct($product3);

    expect($group->products)->toHaveCount(3);
    expect($group->hasProduct($product1))->toBeTrue();
    expect($group->hasProduct($product2))->toBeTrue();
    expect($group->hasProduct($product3))->toBeTrue();

    $this->assertDatabaseHas('pim_group_has_product', [
        'group_id' => $group->id,
        'product_id' => $product1->id,
    ]);
    $this->assertDatabaseHas('pim_group_has_product', [
        'group_id' => $group->id,
        'product_id' => $product2->id,
    ]);
    $this->assertDatabaseHas('pim_group_has_product', [
        'group_id' => $group->id,
        'product_id' => $product3->id,
    ]);
});

it('can check if group has a specific product', function () {
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

    expect($group->hasProduct($product))->toBeFalse();

    $group->addProduct($product);

    expect($group->hasProduct($product))->toBeTrue();
});
