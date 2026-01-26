<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Automatically load all commands in app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // Load routes/console.php if it exists
        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run your daily notification command every day at 00:00
        $schedule->command('notification:daily')->dailyAt('00:00');

        // For testing, you can run every minute
        // $schedule->command('notification:daily')->everyMinute();
    }
}
