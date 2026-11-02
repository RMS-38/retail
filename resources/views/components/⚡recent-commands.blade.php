
<?php

use Livewire\Component;

new class extends Component {
    public array $commands = [
        [
            'reference' => 'CMD-1048',
            'client' => 'M. Andry',
            'status' => 'Paid',
        ],
        [
            'reference' => 'CMD-1047',
            'client' => 'S. Rina',
            'status' => 'Pending',
        ],
        [
            'reference' => 'CMD-1046',
            'client' => 'T. Hery',
            'status' => 'Paid',
        ],
        [
            'reference' => 'CMD-1045',
            'client' => 'A. Fara',
            'status' => 'Pending',
        ],
    ];
};
?>

<flux:card class="rounded-xl border border-slate-200 p-4 shadow-none">

    <flux:heading size="sm" class="mb-3">
        Recent commands
    </flux:heading>

    <div class="divide-y divide-slate-100">

        @foreach ($commands as $command)
            <div wire:key="{{ $command['reference'] }}"
                 class="flex items-center justify-between py-2">

                <div class="space-y-0.5">
                    <flux:text size="xs" class="font-semibold text-slate-800 dark:text-white">
                        #{{ $command['reference'] }}
                    </flux:text>

                    <flux:text size="xs" class="text-slate-400">
                        {{ $command['client'] }}
                    </flux:text>
                </div>

                @if ($command['status'] === 'Paid')
                    <flux:text size="xs" class="font-semibold text-emerald-500">
                        Paid
                    </flux:text>
                @else
                    <flux:text size="xs" class="font-semibold text-amber-500">
                        Pending
                    </flux:text>
                @endif

            </div>
        @endforeach

    </div>

</flux:card>
