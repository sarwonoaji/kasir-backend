<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{
    // Buka session kasir
    public function open(Request $request)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        /** @var int $userId */
        $userId = Auth::id();
        $activeSession = CashierSession::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            return response()->json(['message' => 'Anda sudah membuka session'], 400);
        }

        $session = CashierSession::create([
            'user_id' => $userId,
            'shift_id' => $validated['shift_id'],
            'opened_at' => now(),
            'opening_balance' => $validated['opening_balance'],
            'status' => 'open',
        ]);

        return response()->json($session, 201);
    }

    // Tutup session kasir
    public function close(Request $request, $id)
    {
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $session = CashierSession::findOrFail($id);

        /** @var int $userId */
        $userId = Auth::id();
        if ($session->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $session->update([
            'closed_at' => now(),
            'closing_balance' => $validated['closing_balance'],
            'notes' => $validated['notes'],
            'status' => 'closed',
        ]);

        return response()->json($session);
    }

    // Lihat session aktif
    public function activeSession()
    {
        /** @var int $userId */
        $userId = Auth::id();
        $session = CashierSession::where('user_id', $userId)
            ->where('status', 'open')
            ->with(['shift', 'user'])
            ->first();

        return response()->json($session);
    }

    // History session
    public function history(Request $request)
    {
        /** @var int $userId */
        $userId = Auth::id();
        $sessions = CashierSession::where('user_id', $userId)
            ->with(['shift', 'user'])
            ->orderBy('opened_at', 'desc')
            ->paginate(10);

        return response()->json($sessions);
    }
}