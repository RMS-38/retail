<?php

use App\Models\Client;
use App\Models\Command;
use App\Models\Product;
use App\Models\ProductUnit;
use Livewire\Livewire;

test('creates a command with its selected products', function () {
    $client = Client::query()->create([
        'name' => 'Test Client',
        'address' => '1 Test Street',
        'phone' => '555-0100',
    ]);
    $product = Product::factory()->create();
    $unit = ProductUnit::query()->create([
        'product_id' => $product->id,
        'unit' => 'box',
        'price' => 12.5,
    ]);

    Livewire::test('pages::commands.command-form')
        ->assertSee('Add a product')
        ->dispatch('client-selected', id: $client->id)
        ->dispatch('command-products-updated', products: [[
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 2,
            'unit_price' => 1,
        ]])
        ->set('reference', 'CMD-001')
        ->set('amount_paid', 0)
        ->set('payment_status', 'unpaid')
        ->call('save')
        ->assertRedirect(route('commands.index'));

    $command = Command::query()->sole();

    expect((float) $command->total_amount)->toBe(25.0);

    $this->assertDatabaseHas('command_product', [
        'command_id' => $command->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 12.5,
    ]);
});

test('adds, edits, and removes product lines', function () {
    $product = Product::factory()->create();
    $unit = ProductUnit::query()->create([
        'product_id' => $product->id,
        'unit' => 'box',
        'price' => 12.5,
    ]);

    Livewire::test('commands.products')
        ->call('selectProduct', $product->id)
        ->assertSet('productList.0.quantity', 1)
        ->call('editProduct', 0)
        ->set('productList.0.quantity', 3)
        ->call('saveProduct', 0)
        ->assertSet('productList.0.unit_id', $unit->id)
        ->assertSet('productList.0.quantity', 3)
        ->call('removeProduct', 0)
        ->assertSet('productList', [])
        ->assertDispatched('command-products-updated', products: []);
});

test('updates the command total and removes deleted product lines', function () {
    $client = Client::query()->create([
        'name' => 'Test Client',
        'address' => '1 Test Street',
        'phone' => '555-0100',
    ]);
    $product = Product::factory()->create();
    $unit = ProductUnit::query()->create([
        'product_id' => $product->id,
        'unit' => 'box',
        'price' => 12.5,
    ]);
    $removedProduct = Product::factory()->create();
    ProductUnit::query()->create([
        'product_id' => $removedProduct->id,
        'unit' => 'piece',
        'price' => 5,
    ]);
    $command = Command::query()->create([
        'client_id' => $client->id,
        'reference' => 'CMD-003',
        'amount_paid' => 0,
        'total_amount' => 10,
        'payment_status' => 'unpaid',
    ]);
    $command->products()->attach([
        $product->id => ['quantity' => 1, 'unit_price' => 12.5],
        $removedProduct->id => ['quantity' => 2, 'unit_price' => 5],
    ]);

    Livewire::test('pages::commands.command-form', ['command' => $command])
        ->dispatch('command-products-updated', products: [[
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 3,
            'unit_price' => 12.5,
        ]])
        ->call('save')
        ->assertRedirect(route('commands.index'));

    $command->refresh();

    expect((float) $command->total_amount)->toBe(37.5)
        ->and($command->products()->pluck('products.id')->all())->toBe([$product->id]);

    $this->assertDatabaseHas('command_product', [
        'command_id' => $command->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 12.5,
    ]);
});

test('rejects a product unit that belongs to another product', function () {
    $client = Client::query()->create([
        'name' => 'Test Client',
        'address' => '1 Test Street',
        'phone' => '555-0100',
    ]);
    $product = Product::factory()->create();
    $otherProduct = Product::factory()->create();
    $unit = ProductUnit::query()->create([
        'product_id' => $otherProduct->id,
        'unit' => 'box',
        'price' => 12.5,
    ]);

    Livewire::test('pages::commands.command-form')
        ->dispatch('client-selected', id: $client->id)
        ->dispatch('command-products-updated', products: [[
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 1,
            'unit_price' => 12.5,
        ]])
        ->set('reference', 'CMD-002')
        ->set('amount_paid', 0)
        ->set('payment_status', 'unpaid')
        ->call('save')
        ->assertHasErrors('productItems.0.unit_id');

    $this->assertDatabaseCount('commands', 0);
});
