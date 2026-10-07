<?php

use App\Models\Client;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public ?Client $client = null;

    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';
    #[Validate(['required', 'string', 'max:255'])]
    public string $address = '';
    #[Validate(['required', 'string', 'max:255'])]
    public string $phone = '';

    #[On('create-client')]
    public function create()
    {
        $this->reset();
        $this->modal('client-form')->show();
    }

    #[On('edit-client')]
    public function edit(Client $client)
    {
        $this->client = $client;

        $this->name = $client->name;
        $this->address = $client->address;
        $this->phone = $client->phone;
        $this->modal('client-form')->show();
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->client) {
            $this->client->update($data);
        } else {
            Client::create($data);
        }

        $this->reset();

        Flux::modal('client-form')->close();
    }
};
?>

<div>

    <flux:modal name="client-form" class="md:w-96">
        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    {{ $client ? 'Edit client' : 'New client' }}
                </flux:heading>

                <flux:text class="mt-2">
                    {{ $client
                        ? 'Update client information.'
                        : 'Create a new client.'
                    }}
                </flux:text>
            </div>

            <flux:input
                wire:model="name"
                label="Name"
                placeholder="Client name" />

            <flux:input
                wire:model="address"
                label="Address"
                placeholder="Client address" />

            <flux:input
                wire:model="phone"
                label="Phone"
                placeholder="Client phone" />

            <div class="flex">
                <flux:spacer />

                <flux:button
                    wire:click="save"
                    variant="primary">
                    Save
                </flux:button>
            </div>

        </div>
    </flux:modal>
</div>