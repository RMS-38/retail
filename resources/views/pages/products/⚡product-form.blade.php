<?php

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title("Product form")] class extends Component
{
    public ?Product $product = null;
    public string $name = "";
    public array $units = [];

    public function mount(?Product $product = null)
    {
        $this->product = $product;
        $this->name = $product->name ?? "";
        $this->units = $product
            ? $product->units->map(fn($unit) => [
                'unit'  => $unit->unit,
                'price' => $unit->price,
            ])->toArray()
            : [];
    }

    public function save()
    {
        $data = $this->validate([
            'name'            => ['required', 'string', 'max:255'],
            'units'           => ['required', 'array', 'min:1'],
            'units.*.unit'    => ['required', 'string', 'max:100'],
            'units.*.price'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $units = collect($data['units'])
            ->map(fn($unit) => [
                'unit'  => $unit['unit'],
                'price' => $unit['price'],
            ])
            ->toArray();

        if ($this->product) {
            $this->product->update(['name' => $data['name']]);
            $this->product->units()->delete();
            $this->product->units()->createMany($units);
        } else {
            $product = Product::create(['name' => $data['name']]);
            $product->units()->createMany($units);
        }

        $this->redirectRoute('products.index');
    }
};
?>

<div
    x-data="{
        newUnit: '',
        newPrice: '',
        units: $wire.entangle('units'),

        addUnit() {
            if (!this.newUnit.trim() || !this.newPrice || Number(this.newPrice) <= 0) {
                return;
            }

            this.units.push({
                unit: this.newUnit.trim(),
                price: Number(this.newPrice),
            });

            this.newUnit = '';
            this.newPrice = '';
        },

        removeUnit(index) {
            this.units.splice(index, 1);
        }
    }"
    class="space-y-6 md:w-1/2">
    <h1 class="text-xl font-bold">
        {{ $product ? 'Edit Product' : 'Create Product' }}
    </h1>

    <flux:input
        wire:model="name"
        label="Product"
        placeholder="Product name" />

    {{-- Add new unit --}}
    <div class="space-y-3">
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <flux:input
                    x-model="newUnit"
                    label="Unit"
                    placeholder="Unit" />
            </div>
            <div class="flex-1">
                <flux:input
                    x-model="newPrice"
                    label="Price"
                    type="number"
                    step="0.01"
                    placeholder="0.00" />
            </div>
        </div>

        <flux:button
            type="button"
            @click="addUnit()"
            icon="plus">
            Add unit
        </flux:button>
    </div>

    {{-- Existing / added units --}}
    <template x-for="(item, i) in units" :key="i">
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <flux:input
                    x-model="item.unit"
                    placeholder="Unit" />
            </div>

            <div class="flex-1">
                <flux:input
                    x-model="item.price"
                    type="number"
                    step="0.01"
                    placeholder="Price" />
            </div>

            <flux:button
                type="button"
                variant="danger"
                icon="x-mark"
                @click="removeUnit(i)" />
        </div>
    </template>

    <div class="flex justify-end">
        <flux:button
            wire:click="save"
            variant="primary">
            {{ $product ? 'Update' : 'Save' }}
        </flux:button>
    </div>
</div>

<script>

</script>