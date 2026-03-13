<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('per_page')) {
            $perPage = $request->per_page;
            $products = $query->paginate($perPage);

            return response()->json([
                'data' => $products->items(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]);
        }

        return $query->get();
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string|unique:products,barcode',
            'name'    => 'required',
            'price'   => 'required|integer|min:0',
            'stock'   => 'nullable|integer|min:0',
            'unit'    => 'required|string',
            'description' => 'nullable|string',
        ]);

        $data['stock'] = $data['stock'] ?? 0;

        // if (empty($data['barcode'])) {
        //     $data['barcode'] = $this->generateEan13();
        // }

        return Product::create($data);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string',
            'name'  => 'required',
            'price' => 'required|integer|min:0',
            'unit'  => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $product->update($data);
        return $product;
    }

    public function destroy(Product $product)
    {
        $product->update([
            'is_active' => false,
        ]);

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }

    public function scan($barcode)
    {
        return Product::where('barcode', $barcode)
            ->where('is_active', true)
            ->firstOrFail();
    }

    // private function generateEan13()
    // {
    //     // Generate 12 random digits
    //     $digits = '';
    //     for ($i = 0; $i < 12; $i++) {
    //         $digits .= rand(0, 9);
    //     }

    //     // Calculate checksum
    //     $sum = 0;
    //     for ($i = 0; $i < 12; $i++) {
    //         $weight = ($i % 2 == 0) ? 1 : 3;
    //         $sum += (int)$digits[$i] * $weight;
    //     }
    //     $checksum = (10 - ($sum % 10)) % 10;

    //     return $digits . $checksum;
    // }

    // public function generateBarcode()
    // {
    //     return response()->json([
    //         'barcode' => $this->generateEan13()
    //     ]);
    // }
}
