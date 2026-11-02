
<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Command;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    // À remplacer par l'identifiant du client
    public int $clientId = 1;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function getCommandsProperty()
    {
        return Command::query()
            ->where('client_id', $this->clientId)
            ->when($this->search, function ($query) {
                $query->where('reference', 'like', '%' . $this->search . '%');
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('payment_status', $this->status);
            })
            ->latest()
            ->paginate(10);
    }
};
?>


<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="space-y-1">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#">
                    Clients
                </flux:breadcrumbs.item>
                <flux:breadcrumbs.item>
                    Orders
                </flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:heading size="xl">
                Customer Orders
            </flux:heading>

            <flux:text class="text-sm text-zinc-500">
                View and manage this customer's order history.
            </flux:text>
        </div>

        <flux:button variant="primary" icon="plus">
            New Order
        </flux:button>

    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <flux:card>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">Total Orders</flux:text>
                <flux:icon.shopping-bag class="size-5 text-blue-500"/>
            </div>

            <flux:heading size="2xl" class="mt-2">
                {{ Command::where('client_id', $clientId)->count() }}
            </flux:heading>

            <flux:text class="mt-1 text-xs text-zinc-500">
                All registered orders
            </flux:text>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">Paid Orders</flux:text>
                <flux:icon.check-circle class="size-5 text-green-500"/>
            </div>

            <flux:heading size="2xl" class="mt-2">
                {{ Command::where('client_id', $clientId)->where('payment_status', 'paid')->count() }}
            </flux:heading>

            <flux:text class="mt-1 text-xs text-zinc-500">
                Successfully paid orders
            </flux:text>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">Pending Payments</flux:text>
                <flux:icon.clock class="size-5 text-amber-500"/>
            </div>

            <flux:heading size="2xl" class="mt-2">
                {{ Command::where('client_id', $clientId)->where('payment_status', 'pending')->count() }}
            </flux:heading>

            <flux:text class="mt-1 text-xs text-zinc-500">
                Payments awaiting collection
            </flux:text>
        </flux:card>

    </div>

    {{-- Orders --}}
    <flux:card class="space-y-4">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <flux:heading size="lg">
                    Order History
                </flux:heading>

                <flux:text class="text-xs text-zinc-500">
                    View all orders placed by this customer.
                </flux:text>
            </div>

            <flux:badge color="blue">
                {{ $this->commands->total() }} orders
            </flux:badge>

        </div>

        {{-- Search and filters --}}
        <div class="flex flex-col gap-3 sm:flex-row">

            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by reference..."
                    icon="magnifying-glass"
                    clearable
                />
            </div>

            <div class="w-full sm:w-48">
                <flux:select wire:model.live="status">
                    <flux:select.option value="all">
                        All Statuses
                    </flux:select.option>

                    <flux:select.option value="paid">
                        Paid
                    </flux:select.option>

                    <flux:select.option value="pending">
                        Pending
                    </flux:select.option>
                </flux:select>
            </div>

        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                    <tr class="text-xs text-zinc-500">
                        <th class="py-3 pr-4 font-medium">Reference</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Amount</th>
                        <th class="px-4 py-3 font-medium">Payment Status</th>
                        <th class="py-3 pl-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @forelse ($this->commands as $command)

                        <tr wire:key="command-{{ $command->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                            <td class="py-4 pr-4">
                                <span class="font-medium text-zinc-800 dark:text-white">
                                    {{ $command->reference }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-zinc-500">
                                {{ $command->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-4 font-medium">
                                {{ number_format($command->total_amount, 0, ',', ' ') }}
                            </td>

                            <td class="px-4 py-4">
                                @if ($command->payment_status === 'paid')
                                    <flux:badge color="green" size="sm">
                                        Paid
                                    </flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">
                                        Pending
                                    </flux:badge>
                                @endif
                            </td>

                            <td class="py-4 pl-4">
                                <div class="flex justify-end gap-2">

                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        title="View"
                                    />

                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil"
                                        title="Edit"
                                    />

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="py-12 text-center">

                                <flux:icon.inbox
                                    class="mx-auto size-10 text-zinc-400"
                                />

                                <flux:heading size="sm" class="mt-3">
                                    No orders found
                                </flux:heading>

                                <flux:text class="mt-1 text-xs text-zinc-500">
                                    This customer has no orders matching your search.
                                </flux:text>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        <div class="border-t border-zinc-100 pt-4 dark:border-zinc-800">
            {{ $this->commands->links() }}
        </div>

    </flux:card>

</div>
