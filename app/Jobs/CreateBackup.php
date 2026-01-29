<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Artisan;

class CreateBackup
{

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('backup:run', ['--only-db' => true]);

        if ($exitCode !== 0) {
            throw new \Exception('Backup command failed with exit code: ' . $exitCode);
        }
    }
}
