<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean {--days=7 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete log files older than a given number of days (default 7)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $path = storage_path('logs');

        if (!is_dir($path)) {
            $this->info('No logs directory found.');
            return 0;
        }

        $pattern = $path . DIRECTORY_SEPARATOR . '*.log';
        $files = glob($pattern);
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $age = $now - filemtime($file);
            if ($age > ($days * 86400)) {
                @unlink($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} log file(s) older than {$days} day(s).");

        return 0;
    }
}
