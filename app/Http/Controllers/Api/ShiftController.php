<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    // Tampil semua shift
    public function index()
    {
        $shifts = Shift::all();
        return response()->json($shifts);
    }

    // Tambah shift baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:shifts',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $shift = Shift::create($validated);
        return response()->json($shift, 201);
    }

    // Lihat detail shift
    public function show($id)
    {
        $shift = Shift::with('cashierSessions')->findOrFail($id);
        return response()->json($shift);
    }

    // Update shift
    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:shifts,name,' . $id,
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
        ]);

        $shift->update($validated);
        return response()->json($shift);
    }

    // Hapus shift
    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();
        return response()->json(['message' => 'Shift berhasil dihapus']);
    }
}
