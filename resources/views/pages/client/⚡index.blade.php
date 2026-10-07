<?php

use App\Models\Client;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title("Clients")] class extends Component
{
    use WithPagination;

    public string $search = "";

    #[Computed]
    public function clients()
    {
        return Client::query()
            ->filter($this->search)
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function delete(Client $client)
    {
        $client->delete();

        $this->dispatch('client-deleted');
    }
};
?>

<div>
    <div class="flex items-center justify-between gap-4 my-3">
        <div>
            <flux:input
                icon="magnifying-glass"
                placeholder="Search..."
                wire:model.live.debounce.400ms="search" />
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
                                <flux:menu.item
                                    icon="trash"
                                    variant="danger"
                                    wire:click="delete({{ $client->id }})"
                                    wire:confirm="Are you sure you want to delete this client?">
                                    Delete
                                </flux:menu.item>
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