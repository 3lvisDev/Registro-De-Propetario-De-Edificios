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
        // Clean audit logs older than 90 days - Requisito 28.6
        // Run daily at 2:00 AM
        $schedule->command('audit:clean --days=90')
            ->daily()
            ->at('02:00')
            ->onSuccess(function () {
                \Log::info('Audit logs cleanup completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Audit logs cleanup failed');
            });
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
