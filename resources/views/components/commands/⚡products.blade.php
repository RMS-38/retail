<?php

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    /**
     * @var array<int, array{product_id: int, unit_id: ?int, quantity: int, unit_price: float}>
     */
    public array $productList = [];

    public ?int $editingIndex = null;

    /**
     * @var array{product_id: int, unit_id: ?int, quantity: int, unit_price: float}|null
     */
    public ?array $editingOriginal = null;

    /**
     * @param  array<int, array{product_id: int, unit_id: ?int, quantity: int, unit_price: float}>  $initialProducts
     */
    public function mount(array $initialProducts = []): void
    {
        $this->productList = array_values($initialProducts);
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->with('units')
            ->filter($this->search)
            ->get();
    }

    #[Computed]
    public function selectedProducts(): Collection
    {
        if ($this->productList === []) {
            return Product::query()->whereRaw('1 = 0')->get();
        }

        return Product::query()
            ->with('units')
            ->whereIn('id', array_column($this->productList, 'product_id'))
            ->get()
            ->keyBy('id');
    }

    public function selectProduct(int $id): void
    {
        $product = Product::query()->with('units')->findOrFail($id);
        $unit = $product->units->first();

        if (! $unit) {
            $this->addError('productList', 'This product has no units available.');

            return;
        }

        $this->resetErrorBag('productList');

        foreach ($this->productList as $index => $line) {
            if ($line['product_id'] === $product->id) {
                $this->productList[$index]['quantity']++;
                $this->dispatchProductsUpdated();
                $this->modal('select-product')->close();

                return;
            }
        }

        $this->productList[] = [
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 1,
            'unit_price' => (float) $unit->price,
        ];

        $this->dispatchProductsUpdated();
        $this->modal('select-product')->close();
    }

    public function updatedProductList(mixed $value, string $key): void
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, null);

        if (! isset($this->productList[$index])) {
            return;
        }

        if ($field === 'unit_id') {
            if ($value === null || $value === '') {
                $this->productList[$index]['unit_id'] = null;
            } else {
                $product = Product::query()
                    ->with('units')
                    ->findOrFail($this->productList[$index]['product_id']);
                $unit = $product->units->firstWhere('id', (int) $value);

                if (! $unit) {
                    throw ValidationException::withMessages([
                        "productList.{$index}.unit_id" => 'Select a valid unit for this product.',
                    ]);
                }

                $this->productList[$index]['unit_id'] = $unit->id;
                $this->productList[$index]['unit_price'] = (float) $unit->price;
            }
        }

        $this->dispatchProductsUpdated();
    }

    public function editProduct(int $index): void
    {
        abort_unless(array_key_exists($index, $this->productList), 404);

        $this->editingIndex = $index;
        $this->editingOriginal = $this->productList[$index];
    }

    public function saveProduct(int $index): void
    {
        abort_unless($this->editingIndex === $index && isset($this->productList[$index]), 404);

        $this->validate([
            "productList.{$index}.quantity" => ['required', 'integer', 'min:1'],
            "productList.{$index}.unit_id" => ['nullable', 'integer', 'exists:product_units,id'],
        ]);

        $line = $this->productList[$index];

        if ($line['unit_id'] !== null) {
            $product = Product::query()
                ->with('units')
                ->findOrFail($line['product_id']);
            $unit = $product->units->firstWhere('id', $line['unit_id']);

            if (! $unit) {
                throw ValidationException::withMessages([
                    "productList.{$index}.unit_id" => 'Select a valid unit for this product.',
                ]);
            }

            $this->productList[$index]['unit_price'] = (float) $unit->price;
        }

        $this->editingIndex = null;
        $this->editingOriginal = null;
        $this->dispatchProductsUpdated();
    }

    public function cancelEditing(): void
    {
        if ($this->editingIndex !== null && $this->editingOriginal !== null) {
            $this->productList[$this->editingIndex] = $this->editingOriginal;
        }

        $this->editingIndex = null;
        $this->editingOriginal = null;
        $this->resetValidation();
        $this->dispatchProductsUpdated();
    }

    public function removeProduct(int $index): void
    {
        abort_unless(array_key_exists($index, $this->productList), 404);

        unset($this->productList[$index]);
        $this->productList = array_values($this->productList);

        if ($this->editingIndex === $index) {
            $this->editingIndex = null;
            $this->editingOriginal = null;
        } elseif ($this->editingIndex !== null && $this->editingIndex > $index) {
            $this->editingIndex--;
        }

        $this->dispatchProductsUpdated();
    }

    private function dispatchProductsUpdated(): void
    {
        $this->dispatch('command-products-updated', products: $this->productList);
    }
};
?>

<div class="space-y-4">
    <flux:card>
        <flux:heading size="lg">Products in this command</flux:heading>

        <flux:table class="mt-4">
            <flux:table.columns>
                <flux:table.column>Product</flux:table.column>
                <flux:table.column>Unit</flux:table.column>
                <flux:table.column>Quantity</flux:table.column>
                <flux:table.column align="end">Unit price</flux:table.column>
                <flux:table.column align="end">Subtotal</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($productList as $index => $line)
                    @php
                        $product = $this->selectedProducts[$line['product_id']] ?? null;
                        $unit = $product?->units->firstWhere('id', $line['unit_id']);
                    @endphp

                    <flux:table.row :key="$line['product_id']">
                        <flux:table.cell variant="strong">
                            {{ $product?->name ?? 'Unavailable product' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($editingIndex === $index)
                                <div class="w-25">
                                    <flux:select wire:model.live="productList.{{ $index }}.unit_id">
                                        @if ($line['unit_id'] === null)
                                            <flux:select.option :key="'saved-price-'.$index" value="">Saved price</flux:select.option>
                                        @endif

                                        @foreach ($product?->units ?? [] as $productUnit)
                                            <flux:select.option :key="$productUnit->id" value="{{ $productUnit->id }}">
                                                {{ $productUnit->unit }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                <flux:error name="productList.{{ $index }}.unit_id" />
                            @else
                                {{ $unit?->unit ?? 'Saved price' }}
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($editingIndex === $index)
                                <div>
                                    <flux:input
                                        type="number"
                                        min="1"
                                        step="1"
                                        wire:model.blur="productList.{{ $index }}.quantity" />
                                    <flux:error name="productList.{{ $index }}.quantity" />
                                </div>
                            @else
                                {{ $line['quantity'] }}
                            @endif
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            {{ number_format($line['unit_price'], 2) }}
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            {{ number_format($line['unit_price'] * $line['quantity'], 2) }}
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            @if ($editingIndex === $index)
                                <div class="flex justify-end gap-2">
                                    <flux:button size="sm" wire:click="saveProduct({{ $index }})">
                                        Save
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" wire:click="cancelEditing">
                                        Cancel
                                    </flux:button>
                                </div>
                            @else
                                <div class="flex justify-end gap-2">
                                    <flux:button size="sm" variant="ghost" wire:click="editProduct({{ $index }})">
                                        Edit
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="removeProduct({{ $index }})">
                                        Remove
                                    </flux:button>
                                </div>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach

                <flux:table.row>
                    <flux:table.cell>
                        <flux:modal.trigger name="select-product">
                            <flux:button size="sm" icon="plus">Add a product</flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                </flux:table.row>
            </flux:table.rows>
        </flux:table>

        <flux:error name="productList" />

        <div class="mt-4 flex justify-end gap-2 text-sm font-semibold">
            <span>Command total</span>
            <span>
                {{ number_format(collect($productList)->sum(fn ($line) => $line['unit_price'] * $line['quantity']), 2) }}
            </span>
        </div>
    </flux:card>

    <flux:modal name="select-product" flyout>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Add a product</flux:heading>
            </div>

            <flux:input
                icon="magnifying-glass"
                placeholder="Search products..."
                wire:model.live.debounce.400ms="search" />

            <div class="space-y-2">
                @forelse ($this->products as $product)
                    <flux:card :key="$product->id" class="flex items-center justify-between gap-4">
                        <div>
                            <flux:heading>{{ $product->name }}</flux:heading>
                            @if ($product->units->isEmpty())
                                <flux:text>This product has no units available.</flux:text>
                            @endif
                        </div>

                        <flux:button
                            size="sm"
                            wire:click="selectProduct({{ $product->id }})"
                            :disabled="$product->units->isEmpty()">
                            Add
                        </flux:button>
                    </flux:card>
                @empty
                    <flux:text>No products found.</flux:text>
                @endforelse
            </div>
        </div>
    </flux:modal>
</div>
