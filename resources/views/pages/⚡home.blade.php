<?php

use Livewire\Component;

new class extends Component
{
    public array $cards = [
        [
            "title" => "TOTAL REVENUE",
            "sub" => "4 850 000 Ar",
            "trend" => "+12.8%",
            "color" => "blue",
        ],
        [
            "title" => "COMMANDS",
            "sub" => "128",
            "trend" => "+8.2%",
            "color" => "green",
        ],
        [
            "title" => "CLIENTS",
            "sub" => "86",
            "trend" => "+5.4%",
            "color" => "purple",
        ],
        [
            "title" => "UNPAID",
            "sub" => "620 000 Ar",
            "trend" => "12 pending",
            "color" => "orange",
        ],
    ];
};
    
?>

<div class="w-full p-3 space-y-4">
    <div class="flex gap-3 flex-col md:flex-row items-center justify-between mt-8">
        @foreach ($cards as $card)
            <flux:card class="w-50 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-{{$card['color']}}-500"></span>
                    <flux:heading class="text-{{$card['color']}}-400">{{$card["title"]}}</flux:heading>
                </div>
                
                <flux:text class="font-bold">{{$card["sub"]}}</flux:text>
                <flux:text class="text-xs" color="{{ $card['color'] }}">{{$card['trend']}}</flux:text>
            </flux:card>
        @endforeach

        
        
    </div>
    <div class="flex gap-3 w-full">
        <div class="flex-1">
            <livewire:revenue-chart />
        </div>
        <div class="w-1/3">
            <livewire:recent-commands/>
        </div>
        
    </div>
</div>