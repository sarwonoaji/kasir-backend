<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\CreateBackup;

class BackupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $files = Storage::disk('local')->files('Laravel');

        $backups = collect($files)->map(function ($file) {
            $path = storage_path('app/private/' . $file);
            $size = filesize($path);
            $date = date('Y-m-d H:i:s', filemtime($path));

            return [
                'filename' => basename($file),
                'size' => $this->formatBytes($size),
                'date' => $date,
                'path' => $file
            ];
        })->sortByDesc('date')->values();

        return response()->json($backups);
    }

    public function download($filename)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = 'Laravel/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        $fullPath = Storage::disk('local')->path($path);
        return response()->download($fullPath, $filename);
    }

    public function destroy($filename)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = 'Laravel/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        Storage::disk('local')->delete($path);

        return response()->json(['message' => 'Backup deleted successfully']);
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Jalankan job langsung
            (new CreateBackup())->handle();
            return response()->json(['message' => 'Backup created successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Backup failed: ' . $e->getMessage()], 500);
        }
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
