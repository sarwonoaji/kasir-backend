<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOut;
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User tidak terautentikasi'], 401);
        }

        $session = CashierSession::where('user_id', $userId)->where('status', 'open')->first();

        if (!$session) {
            return response()->json(['message' => 'Tidak ada sesi kasir yang terbuka untuk user ini'], 403);
        }

        /** @var CashierSession $session */

        DB::transaction(function () use ($request, $userId, $session) {
            /** @var CashierSession $session */

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
                'casher' => $request->casher,
                'user_id' => $userId,
                'session_cashier_id' => $session->id
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

    public function getTodayTransactions()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User tidak terautentikasi'], 401);
        }

        $today = now()->toDateString();

        $transactions = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereDate('date', $today)
            ->with('details.product')
            ->orderByDesc('id')
            ->get();

        return response()->json($transactions);
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

    public function getDailyReport()
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $report = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereDate('date', $today)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        return response()->json([
            'date' => $today,
            'user_id' => $userId,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => ProductOut::where('user_id', $userId)
                ->where('isDeleted', false)
                ->whereDate('date', $today)
                ->with('details.product')
                ->get()
        ]);
    }

    public function getMonthlyReport()
    {
        $userId = Auth::id();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $report = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        return response()->json([
            'month' => $currentMonth,
            'year' => $currentYear,
            'user_id' => $userId,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => ProductOut::where('user_id', $userId)
                ->where('isDeleted', false)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->with('details.product')
                ->get()
        ]);
    }

    public function getShiftReport($shiftId)
    {
        $userId = Auth::id();

        // Get session IDs for the shift
        $sessionIds = CashierSession::where('shift_id', $shiftId)
            ->where('user_id', $userId)
            ->pluck('id');

        $report = ProductOut::whereIn('session_cashier_id', $sessionIds)
            ->where('isDeleted', false)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        return response()->json([
            'shift_id' => $shiftId,
            'user_id' => $userId,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => ProductOut::whereIn('session_cashier_id', $sessionIds)
                ->where('isDeleted', false)
                ->with('details.product')
                ->get()
        ]);
    }

    public function getAllUsersDailyReport()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $today = now()->toDateString();

        $report = ProductOut::where('isDeleted', false)
            ->whereDate('date', $today)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        return response()->json([
            'date' => $today,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => ProductOut::where('isDeleted', false)
                ->whereDate('date', $today)
                ->with('details.product', 'user')
                ->get()
        ]);
    }

    public function getAllUsersMonthlyReport()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $report = ProductOut::where('isDeleted', false)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        return response()->json([
            'month' => $currentMonth,
            'year' => $currentYear,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => ProductOut::where('isDeleted', false)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->with('details.product', 'user')
                ->get()
        ]);
    }

    public function getAllUsersShiftReport($shiftId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get session IDs for the shift (all users)
        $sessionIds = CashierSession::where('shift_id', $shiftId)
            ->pluck('id');

        $report = ProductOut::whereIn('session_cashier_id', $sessionIds)
            ->where('isDeleted', false)
            ->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        return response()->json([
            'shift_id' => $shiftId,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => ProductOut::whereIn('session_cashier_id', $sessionIds)
                ->where('isDeleted', false)
                ->with('details.product', 'user')
                ->get()
        ]);
    }
}
