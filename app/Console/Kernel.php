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
        // Clean up old notifications weekly
        $schedule->command('notifications:cleanup')->weekly();

        // Process pending orders every hour
        $schedule->command('orders:process-pending')->hourly();

        // Generate daily sales report
        $schedule->command('report:generate --period=day')->dailyAt('23:55');

        // Generate monthly sales report
        $schedule->command('report:generate --period=month')->monthlyOn(1, '01:00');

        // Clear expired cache entries
        $schedule->command('cache:prune-stale-tags')->daily();

        // Database backup (using spatie/laravel-backup if installed)
        $schedule->command('backup:clean')->daily()->at('01:30');
        $schedule->command('backup:run')->daily()->at('02:00');

        // Queue monitoring
        $schedule->command('queue:monitor redis --max=1000')->everyFiveMinutes();

        // Restart queue workers weekly to prevent memory leaks
        $schedule->command('queue:restart')->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
