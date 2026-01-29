<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOut;
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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

    

    public function getFilteredReport(Request $request)
    {
        $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User tidak terautentikasi'], 401);
        }

        $queryForReport = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $sessionIds = CashierSession::where('shift_id', $request->shift_id)
                ->where('user_id', $userId)
                ->pluck('id');
            $queryForReport->whereIn('session_cashier_id', $sessionIds);
        }

        $report = $queryForReport->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        $queryForTransactions = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $queryForTransactions->whereIn('session_cashier_id', $sessionIds);
        }

        $transactions = $queryForTransactions->with(['details' => function($q) {
            $q->where('isDeleted', false);
        }, 'details.product'])->get();

        return response()->json([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift_id' => $request->shift_id,
            'user_id' => $userId,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => $transactions
        ]);
    }

    public function getAllUsersFilteredReport(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $queryForReport = ProductOut::where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $sessionIds = CashierSession::where('shift_id', $request->shift_id)
                ->pluck('id');
            $queryForReport->whereIn('session_cashier_id', $sessionIds);
        }

        $report = $queryForReport->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        $queryForTransactions = ProductOut::where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $queryForTransactions->whereIn('session_cashier_id', $sessionIds);
        }

        $transactions = $queryForTransactions->with(['details' => function($q) {
            $q->where('isDeleted', false);
        }, 'details.product', 'user'])->get();

        return response()->json([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift_id' => $request->shift_id,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => $transactions
        ]);
    }

    public function getFilteredReportPdf(Request $request)
    {
        $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User tidak terautentikasi'], 401);
        }

        $queryForReport = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $sessionIds = CashierSession::where('shift_id', $request->shift_id)
                ->where('user_id', $userId)
                ->pluck('id');
            $queryForReport->whereIn('session_cashier_id', $sessionIds);
        }

        $report = $queryForReport->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        $queryForTransactions = ProductOut::where('user_id', $userId)
            ->where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $queryForTransactions->whereIn('session_cashier_id', $sessionIds);
        }

        $transactions = $queryForTransactions->with(['details' => function($q) {
            $q->where('isDeleted', false);
        }, 'details.product'])->get();

        $data = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift_id' => $request->shift_id,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => $transactions
        ];

        $pdf = Pdf::loadView('pdf.report', $data);
        return $pdf->download('report.pdf');
    }

    public function getAllUsersFilteredReportPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $queryForReport = ProductOut::where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $sessionIds = CashierSession::where('shift_id', $request->shift_id)
                ->pluck('id');
            $queryForReport->whereIn('session_cashier_id', $sessionIds);
        }

        $report = $queryForReport->selectRaw('COUNT(*) as total_transactions, SUM(total) as total_sales')
            ->first();

        $queryForTransactions = ProductOut::where('isDeleted', false)
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->has('shift_id')) {
            $queryForTransactions->whereIn('session_cashier_id', $sessionIds);
        }

        $transactions = $queryForTransactions->with(['details' => function($q) {
            $q->where('isDeleted', false);
        }, 'details.product', 'user'])->get();

        $data = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift_id' => $request->shift_id,
            'total_transactions' => $report->total_transactions ?? 0,
            'total_sales' => $report->total_sales ?? 0,
            'transactions' => $transactions
        ];

        $pdf = Pdf::loadView('pdf.report', $data);
        return $pdf->download('report.pdf');
    }
}
