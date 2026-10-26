<?php

use Livewire\Component;

new class extends Component
{
    //
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
            variant="primary"
            color="blue"
            size="sm"
            href="{{route('commands.create')}}"
            wire:navigate
            icon="plus">
            New Command
        </flux:button>

    </div>
</div>