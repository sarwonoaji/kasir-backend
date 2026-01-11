<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::where('is_active', true)->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|unique:products,barcode',
            'name'    => 'required',
            'price'   => 'required|integer|min:0',
            'stock'   => 'required|integer|min:0',
            'unit'    => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        return Product::create($data);
    }

    // ✅ SHOW BY ID (ADMIN)
    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'  => 'required',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'unit'  => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $product->update($data);
        return $product;
    }

    // ✅ SOFT DELETE POS STYLE
    public function destroy(Product $product)
    {
        $product->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }

    // 🔍 KHUSUS POS SCAN BARCODE
    public function scan($barcode)
    {
        return Product::where('barcode', $barcode)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
