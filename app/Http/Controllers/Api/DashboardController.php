<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Total sales today (all users if admin, else own)
        $queryToday = ProductOut::where('isDeleted', false)->whereDate('date', $today);
        if ($user->role !== 'admin') {
            $queryToday->where('user_id', $user->id);
        }
        $totalSalesToday = $queryToday->sum('total');

        // Total transactions today
        $totalTransactionsToday = $queryToday->count();

        // Total sales this month
        $queryMonth = ProductOut::where('isDeleted', false)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear);
        if ($user->role !== 'admin') {
            $queryMonth->where('user_id', $user->id);
        }
        $totalSalesMonth = $queryMonth->sum('total');

        // Low stock products (e.g., stock < 10)
        $lowStockProducts = Product::where('stock', '<', 10)->count();

        // Recent transactions (last 5)
        $recentTransactions = ProductOut::where('isDeleted', false)
            ->with('details.product', 'user')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'total_sales_today' => $totalSalesToday,
            'total_transactions_today' => $totalTransactionsToday,
            'total_sales_month' => $totalSalesMonth,
            'low_stock_products_count' => $lowStockProducts,
            'recent_transactions' => $recentTransactions
        ]);
    }
}
