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

        // Archiver les comptes bloqués dont la date de début de blocage est échue (toutes les heures)
        $schedule->job(\App\Jobs\ArchiveBlockedAccountsJob::class)
                 ->hourly()
                 ->description('Archiver les comptes bloqués expirés');

        // Désarchiver les comptes bloqués dont la date de fin de blocage est échue (toutes les heures)
        $schedule->job(\App\Jobs\UnarchiveExpiredBlockedAccountsJob::class)
                 ->hourly()
                 ->description('Désarchiver les comptes bloqués expirés');
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
