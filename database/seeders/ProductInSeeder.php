<?php

namespace Database\Seeders;

use App\Models\ProductIn;
use App\Models\ProductInDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductInSeeder extends Seeder
{
    public function run(): void
    {
        // ProductIn 1
        $productIn1 = ProductIn::create([
            'date' => '2026-01-20',
            'no_transaksi' => 'PI-001-20260120',
            'remark' => 'Pengiriman rutin dari supplier',
        ]);

        ProductInDetail::create([
            'product_in_id' => $productIn1->id,
            'product_id' => Product::where('barcode', '8997029300018')->first()->id,
            'quantity' => 100,
            'price' => 4000,
        ]);

        ProductInDetail::create([
            'product_in_id' => $productIn1->id,
            'product_id' => Product::where('barcode', '8997029300025')->first()->id,
            'quantity' => 50,
            'price' => 9000,
        ]);

        // ProductIn 2
        $productIn2 = ProductIn::create([
            'date' => '2026-01-22',
            'no_transaksi' => 'PI-002-20260122',
            'remark' => 'Pembelian tambahan minuman',
        ]);

        ProductInDetail::create([
            'product_in_id' => $productIn2->id,
            'product_id' => Product::where('barcode', '5000112626145')->first()->id,
            'quantity' => 75,
            'price' => 7000,
        ]);

        ProductInDetail::create([
            'product_in_id' => $productIn2->id,
            'product_id' => Product::where('barcode', '5000112733813')->first()->id,
            'quantity' => 60,
            'price' => 7000,
        ]);

        // ProductIn 3
        $productIn3 = ProductIn::create([
            'date' => '2026-01-23',
            'no_transaksi' => 'PI-003-20260123',
            'remark' => 'Stok makanan dan cemilan',
        ]);

        ProductInDetail::create([
            'product_in_id' => $productIn3->id,
            'product_id' => Product::where('barcode', '8992211014815')->first()->id,
            'quantity' => 200,
            'price' => 2000,
        ]);

        ProductInDetail::create([
            'product_in_id' => $productIn3->id,
            'product_id' => Product::where('barcode', '8992191108009')->first()->id,
            'quantity' => 150,
            'price' => 2500,
        ]);
    }
}
