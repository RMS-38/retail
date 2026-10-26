<?php

use App\Models\Client;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public ?int $selectedClientId = null;

    public function mount(?int $initialClientId = null): void
    {
        $this->selectedClientId = $initialClientId;
    }

    #[Computed]
    public function clients()
    {
        return Client::query()->filter($this->search)->get();
    }

    public function selectClient(int $id)
    {
        $this->selectedClientId = $id;
        $this->dispatch('client-selected', id: $id);
        $this->modal('select-client')->close();
    }

    #[On('client-created')]
    public function onClientCreated(int $id): void
    {
        $this->selectedClientId = $id;
    }

    #[Computed]
    public function selectedClient()
    {
        return $this->selectedClientId
        ? Client::find($this->selectedClientId)
        : null;
    }
};
?>

<div>
    <flux:card class="p-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Client information</h2>
        <div class="flex items-center justify-between">
            <div class="mt-3 space-y-1 text-sm text-slate-700">
                <flux:text variant="strong"><span class="font-medium">Client:</span> {{ $this->selectedClient?->name ?? 'No client selected' }}</flux:text>
                <flux:text variant="strong"><span class="font-medium">Address:</span> {{ $this->selectedClient?->address ?? '—' }}</flux:text>
                <flux:text variant="strong"><span class="font-medium">Phone:</span> {{ $this->selectedClient?->phone ?? '—' }}</flux:text>
            </div>
            <div>
                <flux:button.group>
                    
                     <flux:modal.trigger name="select-client">
                        <flux:button>Existing Client</flux:button>
                    </flux:modal.trigger>
                    <flux:button
                        wire:click="$dispatch('create-client')">
                        New client
                    </flux:button>
                </flux:button.group>
            </div>
        </div>
    </flux:card>
<flux:modal name="select-client" flyout>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Client</flux:heading>
            <flux:text class="mt-2"></flux:text>
        </div>

        <flux:input
            icon="magnifying-glass"
            placeholder="Search..."
            wire:model.live.debounce.400ms="search" />

        @foreach ($this->clients as $client)
            <flux:card wire:click="selectClient({{$client->id}})"
            class="cursor-pointer">
                <flux:heading size="lg">{{$client->name}}</flux:heading>
                <flux:text class="mt-2">Tel: {{$client->phone}}</flux:text>
                <flux:text class="mt-2">Address: {{$client->address}}</flux:text>
            </flux:card>
        @endforeach
    </div>
</flux:modal>
<livewire:pages::client.client-form />
</div>