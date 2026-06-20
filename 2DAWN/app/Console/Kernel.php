<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Backups
        $schedule->command('backup:run --only-db')->dailyAt('02:30');
        $schedule->command('backup:run')->weeklyOn(1, '02:45'); // Mondays
        $schedule->command('backup:clean')->dailyAt('03:00');

        // Queue / jobs monitoring (will log if queue grows too large)
        $schedule->command('queue:monitor default --max=50')->everyFiveMinutes();

        // Backup monitor (health checks)
        $schedule->command('backup:monitor')->dailyAt('03:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
