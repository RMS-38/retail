<?php

use App\Models\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Commands')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function commands(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Command::query()
            ->with('client')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('client', function (Builder $clientQuery) use ($search): void {
                            $clientQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
    }
};
?>

<div>
    <div class="my-3 flex items-center justify-between gap-4">
        <div>
            <flux:input
                icon="magnifying-glass"
                placeholder="Search..."
                wire:model.live.debounce.400ms="search" />
        </div>
        <flux:button
            variant="primary"
            color="blue"
            size="sm"
            href="{{ route('commands.create') }}"
            wire:navigate
            icon="plus">
            New Command
        </flux:button>
    </div>

    <flux:card>
        <flux:table bleed container:class="mt-0">
            <flux:table.columns>
                <flux:table.column>Reference</flux:table.column>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column align="end">Total amount</flux:table.column>
                <flux:table.column align="end">Amount paid</flux:table.column>
                <flux:table.column>Payment status</flux:table.column>
                <flux:table.column align="end">Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->commands as $command)
                    <flux:table.row :key="$command->id">
                        <flux:table.cell variant="strong">
                            {{ $command->reference }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $command->client->name }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ number_format((float) $command->total_amount, 2) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            {{ number_format((float) $command->amount_paid, 2) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ ucfirst($command->payment_status) }}
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                href="{{ route('commands.edit', $command) }}"
                                wire:navigate>
                                Edit
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            No commands found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:pagination :paginator="$this->commands" scroll-to />
</div>