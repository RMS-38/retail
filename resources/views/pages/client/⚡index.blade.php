<?php

use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Clients")] class extends Component
{
    #[Computed]
    public function clients()
    {
        
    }
};
?>

<div>
    <flux:card>
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading>Recent customers</flux:heading>
                <flux:text class="mt-1">Your latest customer activity.</flux:text>
            </div>

            <flux:button size="sm" icon="plus">New client</flux:button>
        </div>

        <flux:table bleed container:class="mt-6">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Address</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column align="end">Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($clients as $client)

                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>