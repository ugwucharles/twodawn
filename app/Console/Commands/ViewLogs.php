<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewLogs extends Command
{
    protected $signature = 'logs:view {channel=payments : Log channel to view} {--lines=50 : Number of lines to show} {--tail : Follow log in real-time}';
    protected $description = 'View application logs with filtering';

    public function handle(): int
    {
        $channel = $this->argument('channel');
        $lines = $this->option('lines');
        $tail = $this->option('tail');

        $logPath = storage_path("logs/{$channel}.log");
        
        if (!File::exists($logPath)) {
            $this->error("Log file not found: {$logPath}");
            return self::FAILURE;
        }

        if ($tail) {
            $this->info("Following {$channel} log (Press Ctrl+C to stop)...");
            $this->line('');
            passthru("tail -f -n {$lines} {$logPath}");
        } else {
            $this->info("Last {$lines} lines from {$channel} log:");
            $this->line('');
            $content = File::get($logPath);
            $logLines = explode("\n", $content);
            $recentLines = array_slice($logLines, -$lines);
            
            foreach ($recentLines as $line) {
                if (trim($line)) {
                    $this->line($line);
                }
            }
        }

        return self::SUCCESS;
    }
}
