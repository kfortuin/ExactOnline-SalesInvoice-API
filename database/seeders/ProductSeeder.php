<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()
            ->count(5)
            ->create();

        $this->command->info('5 Products seeded successfully.');
        $products = Product::all(['id', 'name'])->toArray();
        $this->command->table(['ID', 'Name'], $products);
    }
}
