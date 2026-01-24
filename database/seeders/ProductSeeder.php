<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Aqua 600ml',
            'barcode' => '8997029300018',
            'price' => 5000,
            'stock' => 100,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Aqua 1500ml',
            'barcode' => '8997029300025',
            'price' => 10000,
            'stock' => 50,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Coca-Cola 330ml',
            'barcode' => '5000112626145',
            'price' => 8000,
            'stock' => 75,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Sprite 330ml',
            'barcode' => '5000112733813',
            'price' => 8000,
            'stock' => 60,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Teh Pucuk 350ml',
            'barcode' => '8992753000042',
            'price' => 5500,
            'stock' => 80,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Indomie Goreng',
            'barcode' => '8992211014815',
            'price' => 2500,
            'stock' => 200,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Roti Tawar',
            'barcode' => '8992191001235',
            'price' => 15000,
            'stock' => 30,
            'unit' => 'pcs',
        ]);

        Product::create([
            'name' => 'Milkita Coklat',
            'barcode' => '8992191108009',
            'price' => 3000,
            'stock' => 150,
            'unit' => 'pcs',
        ]);
    }
}
