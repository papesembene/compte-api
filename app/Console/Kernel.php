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
        // Archiver les transactions vers Neon quotidiennement à minuit
        $schedule->job(\App\Jobs\ArchiveDailyTransactionsJob::class)
                 ->dailyAt('00:00')
                 ->description('Archiver les transactions vers Neon');

        // Synchroniser les statuts de blocage des comptes toutes les heures
        $schedule->job(\App\Jobs\SyncBlockedAccountsJob::class)
                 ->hourly()
                 ->description('Synchroniser les statuts de blocage des comptes');
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
