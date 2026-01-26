<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierSessionController extends Controller
{
    // Buka session kasir - cashier untuk diri sendiri, admin untuk cashier lain
    public function open(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        
        // Tentukan user_id yang akan di-open session
        $targetUserId = $validated['user_id'] ?? Auth::id();

        // Authorization check
        if ($authUser->role !== 'admin' && $targetUserId !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - hanya admin yang bisa membuka session untuk cashier lain'], 403);
        }

        // Check apakah target user adalah cashier (jika admin membuka untuk user lain)
        if ($authUser->role === 'admin' && $validated['user_id']) {
            $targetUser = \App\Models\User::findOrFail($validated['user_id']);
            if ($targetUser->role !== 'cashier') {
                return response()->json(['message' => 'User harus memiliki role cashier'], 400);
            }
        }

        // Cek apakah sudah ada session aktif
        $activeSession = CashierSession::where('user_id', $targetUserId)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            return response()->json(['message' => 'User sudah memiliki session aktif'], 400);
        }

        $session = CashierSession::create([
            'user_id' => $targetUserId,
            'shift_id' => $validated['shift_id'],
            'opened_at' => now(),
            'opening_balance' => $validated['opening_balance'],
            'status' => 'open',
        ]);

        return response()->json($session, 201);
    }

    // Tutup session kasir - cashier menutup diri sendiri, admin bisa menutup session mana saja
    public function close(Request $request, $id)
    {
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $session = CashierSession::findOrFail($id);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        
        // Authorization check - hanya admin atau owner bisa close session
        if ($authUser->role !== 'admin' && $session->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized - hanya admin atau owner session yang bisa menutup session'], 403);
        }

        $session->update([
            'closed_at' => now(),
            'closing_balance' => $validated['closing_balance'],
            'notes' => $validated['notes'],
            'status' => 'closed',
        ]);

        return response()->json($session);
    }

    // Lihat session aktif - cashier lihat diri sendiri, admin bisa lihat cashier lain
    public function activeSession(Request $request)
    {
        $targetUserId = $request->query('user_id');
        
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        // Authorization check
        if ($targetUserId && $authUser->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized - hanya admin yang bisa lihat session cashier lain'], 403);
        }

        // Gunakan target user_id jika diberikan dan user adalah admin, jika tidak gunakan current user
        $userId = $targetUserId ?? Auth::id();

        $session = CashierSession::where('user_id', $userId)
            ->where('status', 'open')
            ->with(['shift', 'user'])
            ->first();

        return response()->json($session);
    }

    // Get current active session untuk cashier yang sedang login
    public function current()
    {
        /** @var int $userId */
        $userId = Auth::id();
        $session = CashierSession::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return response()->json(['message' => 'No active session'], 404);
        }

        return response()->json([
            'id' => $session->id,
            'user_id' => $session->user_id,
            'shift_id' => $session->shift_id,
            'opening_balance' => $session->opening_balance,
            'closing_balance' => $session->closing_balance,
            'is_open' => $session->status === 'open',
            'opened_at' => $session->opened_at,
            'closed_at' => $session->closed_at,
        ]);
    }

    // History session - cashier lihat history diri sendiri, admin lihat semua
    public function history(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $query = CashierSession::query();

        // Jika bukan admin, hanya tampilkan session user sendiri
        if ($authUser->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $sessions = $query
            ->with(['shift', 'user'])
            ->orderBy('opened_at', 'desc')
            ->paginate(10);

        return response()->json($sessions);
    }
}
