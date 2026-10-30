<?php

use App\Models\Client;
use App\Models\Command;
use Livewire\Livewire;

test('paginates commands and resets to the first page when searching', function () {
    $client = Client::factory()->create(['name' => 'Searchable Client']);

    foreach (range(1, 11) as $number) {
        Command::query()->create([
            'client_id' => $client->id,
            'reference' => sprintf('COMMAND-%03d', $number),
            'amount_paid' => 0,
            'total_amount' => 25,
            'payment_status' => 'unpaid',
        ]);
    }

    Livewire::test('pages::commands.index')
        ->assertSee('COMMAND-011')
        ->assertDontSee('COMMAND-001')
        ->assertSee('Searchable Client')
        ->call('nextPage')
        ->assertSee('COMMAND-001')
        ->assertDontSee('COMMAND-011')
        ->set('search', 'COMMAND-005')
        ->assertSee('COMMAND-005')
        ->assertDontSee('COMMAND-001')
        ->set('search', 'NO-MATCH')
        ->assertSee('No commands found.');
});
