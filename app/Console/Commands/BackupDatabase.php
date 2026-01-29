<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Commands\BackupCommand;

class BackupDatabase extends BackupCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--filename=} {--only-db} {--db-name=*} {--only-files} {--only-to-disk=} {--disable-notifications} {--timeout=} {--tries=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return parent::handle();
    }
}
