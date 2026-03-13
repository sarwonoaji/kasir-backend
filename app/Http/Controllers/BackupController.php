<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Jobs\CreateBackup;
use Symfony\Component\Process\Process;

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
            // Try to start the backup command directly in background (detached)
            try {
                $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
                $artisan = base_path('artisan');

                if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                    // Windows: use start to detach the process (no window)
                    $cmd = array_merge(['cmd', '/c', 'start', '""', '/B'], [$php, $artisan, 'backup:run', '--only-db']);
                    $process = new Process($cmd);
                    $process->setWorkingDirectory(base_path());
                    $process->start();
                } else {
                    // Unix: use nohup and shell to background the process
                    $shell = 'nohup ' . escapeshellcmd($php) . ' ' . escapeshellarg($artisan) . ' backup:run --only-db > /dev/null 2>&1 &';
                    $process = new Process(['sh', '-c', $shell]);
                    $process->setWorkingDirectory(base_path());
                    $process->start();
                }

                return response()->json(['message' => 'Backup started in background']);
            } catch (\Throwable $ex) {
                Log::error('Failed to start background backup process: ' . $ex->getMessage());
                // Fallback: dispatch job to queue (requires worker)
                CreateBackup::dispatch();
                return response()->json(['message' => 'Backup queued; failed to start background process'], 202);
            }
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
