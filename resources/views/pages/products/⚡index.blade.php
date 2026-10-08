<?php

use App\Models\Product;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title("Products")] class extends Component
{
    use WithPagination;

    public string $search = "";

    public array $selectedUnits = [];
    public array $selectedPrices = [];


    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedUnits($unitId, $productId)
    {
        $product = Product::with('units')->find($productId);

        if (!$product) {
            return;
        }

        $selectedUnit = $product->units->firstWhere('id', $unitId);

        $this->selectedPrices[$productId] = $selectedUnit?->price ?? 0;
    }

    #[Computed]
    public function products()
    {
        return Product::with('units')
            ->filter($this->search)
            ->latest()
            ->paginate(2)
            ->withQueryString();
    }
    public function delete(Product $product)
    {
        $product->delete();
        Flux::toast('Product deleted successfully.');
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
            variant="primary"
            color="blue"
            size="sm"
            href="{{route('products.create')}}"
            wire:navigate
            icon="plus">
            New Product
        </flux:button>

    </div>

    <flux:card>

        <flux:table bleed container:class="mt-0">

            <flux:table.columns>
                <flux:table.column>Id</flux:table.column>
                <flux:table.column>Product</flux:table.column>
                <flux:table.column>Unit</flux:table.column>
                <flux:table.column>Price</flux:table.column>
                <flux:table.column align="end">
                    Action
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @foreach ($this->products as $product)

                <flux:table.row :key="$product->id">

                    <flux:table.cell>
                        {{ $product->id }}
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ $product->name }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:select
                            wire:model.live="selectedUnits.{{ $product->id }}">
                            @foreach ($product->units as $unit)
                            <flux:select.option value="{{ $unit->id }}">
                                {{ $unit->unit }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:table.cell>

                    <flux:table.cell>
                        @php
                        $unitId = $selectedUnits[$product->id]
                        ?? $product->units->first()?->id;

                        $unit = $product->units->firstWhere('id', $unitId);
                        @endphp

                        {{ number_format($unit?->price ?? 0, 2) }}
                    </flux:table.cell>

                    <flux:table.cell align="end">

                        <flux:dropdown>

                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="ellipsis-horizontal" />

                            <flux:menu>

                                <flux:menu.item icon="eye">
                                    View
                                </flux:menu.item>

                                <flux:menu.item
                                    icon="pencil-square"
                                    href="{{route('products.edit', $product->id)}}">
                                    Edit
                                </flux:menu.item>

                                <flux:menu.item
                                    icon="trash"
                                    variant="danger"
                                    wire:click="delete({{ $product->id }})"
                                    wire:confirm="Are you sure you want to delete this product?">
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
    <flux:pagination :paginator="$this->products" scroll-to />
</div>