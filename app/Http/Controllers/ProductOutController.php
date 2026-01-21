<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductOutController extends Controller
{
    public function index()
    {
        return ProductOut::where('isDeleted', false)
            ->with('details.product')
            ->orderByDesc('id')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'date' => 'required|date',
            'casher' => 'required',
            'items' => 'required|array',
            'money_received' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'return' => 'nullable|numeric',
            'payment_method' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {

            $productOut = ProductOut::create([
                'customer_name' => $request->customer_name,
                'date' => $request->date,
                'invoice' => 'OUT-' . time(),
                'total' => collect($request->items)->sum('total_price'),
                'money_received' => $request->money_received,
                'discount' => $request->discount,
                'return' => $request->return,
                'payment_method' => $request->payment_method,
                'remark' => $request->remark,
                'casher' => $request->casher
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

           
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak cukup");
                }

     
                $productOut->details()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0,
                    'total_price' => $item['total_price']
                ]);

           
                $product->decrement('stock', $item['quantity']);
            }
        });

        return response()->json([
            'message' => 'Product out berhasil disimpan'
        ], 201);
    }

    public function show($id)
    {
        return ProductOut::with('details.product')
            ->where('isDeleted', false)
            ->findOrFail($id);
    }

    public function destroy($id)
    {
        $productOut = ProductOut::with('details.product')->findOrFail($id);

        DB::transaction(function () use ($productOut) {

            foreach ($productOut->details as $detail) {
                // BALIKKAN STOK
                $detail->product->increment('stock', $detail->quantity);

                $detail->update(['isDeleted' => true]);
            }

            $productOut->update(['isDeleted' => true]);
        });

        return response()->noContent();
    }
}
