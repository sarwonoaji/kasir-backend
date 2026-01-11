<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductIn;
use App\Models\ProductInDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductInController extends Controller
{
    /**
     * List product in
     */
    public function index()
    {
        return ProductIn::with('details.product')
            ->latest()
            ->get();
    }

    /**
     * Store product in
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'remark' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            $productIn = ProductIn::create([
                'date' => $request->date,
                'no_transaksi' => $this->generateNoTransaksi(),
                'remark' => $request->remark,
            ]);

            foreach ($request->items as $item) {

                ProductInDetail::create([
                    'product_in_id' => $productIn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);

                // tambah stok
                Product::where('id', $item['product_id'])
                    ->increment('stock', $item['quantity']);
            }
        });

        return response()->json([
            'message' => 'Product in berhasil disimpan'
        ], 201);
    }

    /**
     * Show detail
     */
    public function show($id)
    {
        return ProductIn::with('details.product')
            ->findOrFail($id);
    }

    /**
     * Update product in
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'remark' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id) {

            $productIn = ProductIn::with('details')->findOrFail($id);

            // rollback stok lama
            foreach ($productIn->details as $detail) {
                Product::where('id', $detail->product_id)
                    ->decrement('stock', $detail->quantity);
            }

            // update header
            $productIn->update([
                'date' => $request->date,
                'remark' => $request->remark,
            ]);

            // hapus detail lama
            $productIn->details()->delete();

            // simpan detail baru + tambah stok
            foreach ($request->items as $item) {

                ProductInDetail::create([
                    'product_in_id' => $productIn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['quantity'] * $item['price'],
                ]);

                Product::where('id', $item['product_id'])
                    ->increment('stock', $item['quantity']);
            }
        });

        return response()->json([
            'message' => 'Product in berhasil diupdate'
        ]);
    }

    /**
     * Delete product in (rollback stok)
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $productIn = ProductIn::with('details')->findOrFail($id);

            // rollback stok
            foreach ($productIn->details as $detail) {
                Product::where('id', $detail->product_id)
                    ->decrement('stock', $detail->quantity);
            }

            // hapus detail & header
            $productIn->details()->delete();
            $productIn->delete();
        });

        return response()->noContent();
    }

    /**
     * Generate nomor transaksi
     */
    private function generateNoTransaksi()
    {
        $date = now()->format('Ymd');
        $count = ProductIn::whereDate('created_at', now()->toDateString())->count() + 1;

        return 'PI-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function print($id)
    {
        $productIn = ProductIn::with('details.product')
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.product-in', [
            'data' => $productIn
        ])->setPaper('A4');

        return $pdf->stream(
            'product-in-' . $productIn->no_transaksi . '.pdf'
        );
    }
}
