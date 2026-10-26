<?php

use App\Models\Client;
use App\Models\Command;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Command form')] class extends Component
{
    public ?Command $command = null;

    public ?int $client_id = null;

    public string $reference = '';

    public string $payment_status = 'unpaid';

    public float $amount_paid = 0;

    public float $total_amount = 0;

    /**
     * @var array<int, array{product_id: int, unit_id: ?int, quantity: int, unit_price: float}>
     */
    public array $productItems = [];

    public function mount(?Command $command = null): void
    {
        $this->command = $command;

        if ($command) {
            $command->load('products.units');
            $this->client_id = $command->client_id;
            $this->reference = $command->reference;
            $this->payment_status = $command->payment_status;
            $this->amount_paid = (float) $command->amount_paid;
            $this->total_amount = (float) $command->total_amount;
            $this->productItems = $command->products->map(function (Product $product): array {
                $savedPrice = (float) $product->pivot->unit_price;
                $matchingUnit = $product->units->first(
                    fn ($unit): bool => round((float) $unit->price, 2) === round($savedPrice, 2)
                );

                return [
                    'product_id' => $product->id,
                    'unit_id' => $matchingUnit?->id,
                    'quantity' => $product->pivot->quantity,
                    'unit_price' => $savedPrice,
                ];
            })->all();
        }
    }

    #[On('client-selected')]
    #[On('client-created')]
    public function setClient(int $id): void
    {
        $this->client_id = $id;
    }

    /**
     * @param  array<int, array{product_id: int, unit_id: ?int, quantity: int, unit_price: float}>  $products
     */
    #[On('command-products-updated')]
    public function setProductItems(array $products): void
    {
        $this->productItems = array_values($products);
    }

    public function save(): void
    {
        $data = $this->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'reference' => ['required', 'string', 'max:255'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'string', 'in:unpaid,partial,paid'],
            'productItems' => ['array'],
            'productItems.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'productItems.*.unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'productItems.*.quantity' => ['required', 'integer', 'min:1'],
            'productItems.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data): void {
            $products = Product::query()
                ->with('units')
                ->whereIn('id', array_column($data['productItems'], 'product_id'))
                ->get()
                ->keyBy('id');
            $existingProducts = $this->command?->products()->get()->keyBy('id') ?? collect();
            $pivotData = [];
            $totalAmount = 0;

            foreach ($data['productItems'] as $index => $item) {
                $product = $products->get((int) $item['product_id']);
                $unitId = $item['unit_id'] ?? null;

                if (! $product) {
                    throw ValidationException::withMessages([
                        "productItems.{$index}.product_id" => 'Select an available product.',
                    ]);
                }

                if ($unitId !== null) {
                    $unit = $product->units->firstWhere('id', (int) $unitId);

                    if (! $unit) {
                        throw ValidationException::withMessages([
                            "productItems.{$index}.unit_id" => 'Select a valid unit for this product.',
                        ]);
                    }

                    $unitPrice = round((float) $unit->price, 2);
                } else {
                    $existingProduct = $existingProducts->get($product->id);

                    if (! $existingProduct) {
                        throw ValidationException::withMessages([
                            "productItems.{$index}.unit_id" => 'Select a unit for this product.',
                        ]);
                    }

                    $unitPrice = (float) $existingProduct->pivot->unit_price;
                }

                $quantity = (int) $item['quantity'];
                $pivotData[$product->id] = [
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
                $totalAmount += $quantity * $unitPrice;
            }

            $data['total_amount'] = round($totalAmount, 2);
            unset($data['productItems']);

            if ($this->command) {
                $this->command->update($data);
                $command = $this->command;
            } else {
                $command = Command::create($data);
            }

            $command->products()->sync($pivotData);
        });

        $this->redirectRoute('commands.index');
    }

    public function getSelectedClientProperty(): ?Client
    {
        return $this->client_id ? Client::find($this->client_id) : null;
    }
};
?>

<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">
        {{ $command ? 'Edit Command' : 'Create Command' }}
    </h1>

    <form wire:submit.prevent="save" class="space-y-5">
        <flux:input
            label="Reference"
            placeholder="Enter command reference"
            wire:model="reference" />
        <livewire:commands.client :initial-client-id="$client_id" />
        
        <livewire:commands.products :initial-products="$productItems" />

        <flux:select
            label="Payment status"
            wire:model="payment_status"
            placeholder="Choose payment status">
            <flux:select.option value="unpaid">Unpaid</flux:select.option>
            <flux:select.option value="partial">Partial</flux:select.option>
            <flux:select.option value="paid">Paid</flux:select.option>
        </flux:select>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">
                {{ $command ? 'Update' : 'Save' }}
            </flux:button>
        </div>
    </form>
</div>
