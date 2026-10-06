<?php

use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title("Clients")] class extends Component
{
    use WithPagination;

    #[Computed]
    public function clients()
    {
        return Client::latest()->paginate(10);
    }
};
?>

<div>
    <div class="flex items-center justify-between gap-4 my-3">
        <div>
            <flux:input icon="magnifying-glass" placeholder="Search..." />
        </div>
        <flux:button
            wire:click="$dispatch('create-client')"
            variant="primary"
            color="blue"
            size="sm"
            icon="plus">
            New client
        </flux:button>
    </div>
    <flux:card>

        <flux:table bleed container:class="mt-0">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Address</flux:table.column>
                <flux:table.column>Phone</flux:table.column>
                <flux:table.column align="end">Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->clients as $client)
                <flux:table.row :key="$client->id">
                    <flux:table.cell>
                        {{ $client->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $client->address }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $client->phone }}
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"></flux:button>
                            <flux:menu>
                                <flux:menu.item icon="eye">View</flux:menu.item>
                                <flux:menu.item
                                    icon="pencil-square"
                                    icon="pencil-square"
                                    wire:click="$dispatch('edit-client', {client: {{ $client->id }} })">
                                    Edit
                                </flux:menu.item>
                                <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
    <flux:pagination :paginator="$this->clients" scroll-to />
    <livewire:pages::client.client-form />
</div>