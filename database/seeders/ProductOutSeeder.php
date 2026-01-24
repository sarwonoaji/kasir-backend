<?php

namespace Database\Seeders;

use App\Models\ProductOut;
use App\Models\ProductOutDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductOutSeeder extends Seeder
{
    public function run(): void
    {
        // ProductOut 1
        $productOut1 = ProductOut::create([
            'date' => '2026-01-23',
            'cashier_id' => 3,
            'total' => 0,
            'payment_method' => 'cash',
        ]);

        $detail1 = ProductOutDetail::create([
            'product_out_id' => $productOut1->id,
            'product_id' => Product::where('barcode', '8997029300018')->first()->id,
            'quantity' => 10,
            'price' => 5000,
            'discount' => 0,
            'subtotal' => 50000,
        ]);

        $detail2 = ProductOutDetail::create([
            'product_out_id' => $productOut1->id,
            'product_id' => Product::where('barcode', '8992211014815')->first()->id,
            'quantity' => 5,
            'price' => 2500,
            'discount' => 0,
            'subtotal' => 12500,
        ]);

        $productOut1->update(['total' => $detail1->subtotal + $detail2->subtotal]);

        // ProductOut 2
        $productOut2 = ProductOut::create([
            'date' => '2026-01-23',
            'cashier_id' => 4,
            'total' => 0,
            'payment_method' => 'transfer',
        ]);

        $detail3 = ProductOutDetail::create([
            'product_out_id' => $productOut2->id,
            'product_id' => Product::where('barcode', '5000112626145')->first()->id,
            'quantity' => 3,
            'price' => 8000,
            'discount' => 0,
            'subtotal' => 24000,
        ]);

        $detail4 = ProductOutDetail::create([
            'product_out_id' => $productOut2->id,
            'product_id' => Product::where('barcode', '8992191001235')->first()->id,
            'quantity' => 2,
            'price' => 15000,
            'discount' => 0,
            'subtotal' => 30000,
        ]);

        $productOut2->update(['total' => $detail3->subtotal + $detail4->subtotal]);

        // ProductOut 3
        $productOut3 = ProductOut::create([
            'date' => '2026-01-24',
            'cashier_id' => 3,
            'total' => 0,
            'payment_method' => 'cash',
        ]);

        $detail5 = ProductOutDetail::create([
            'product_out_id' => $productOut3->id,
            'product_id' => Product::where('barcode', '8992753000042')->first()->id,
            'quantity' => 8,
            'price' => 5500,
            'discount' => 0,
            'subtotal' => 44000,
        ]);

        $detail6 = ProductOutDetail::create([
            'product_out_id' => $productOut3->id,
            'product_id' => Product::where('barcode', '8992191108009')->first()->id,
            'quantity' => 10,
            'price' => 3000,
            'discount' => 0,
            'subtotal' => 30000,
        ]);

        $productOut3->update(['total' => $detail5->subtotal + $detail6->subtotal]);
    }
}
