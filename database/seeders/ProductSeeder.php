<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Farine',
                'units' => [
                    ['unit' => 'kg', 'price' => 3000],
                    ['unit' => 'sack', 'price' => 140000],
                ],
            ],
            [
                'name' => 'Rice',
                'units' => [
                    ['unit' => 'kg', 'price' => 3500],
                    ['unit' => 'sack', 'price' => 160000],
                ],
            ],
            [
                'name' => 'Oil',
                'units' => [
                    ['unit' => 'litre', 'price' => 8000],
                    ['unit' => 'Canister', 'price' => 38000],
                ],
            ],
            [
                'name' => 'Coca-Cola',
                'units' => [
                    ['unit' => 'Bottle', 'price' => 3000],
                    ['unit' => 'Pack', 'price' => 60000],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'name' => $productData['name'],
            ]);

            $product->units()->createMany($productData['units']);
        }
    }
}
