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

        // Bloquer automatiquement les comptes dont la date de début de blocage est atteinte (toutes les heures en production)
        $schedule->job(\App\Jobs\ArchiveExpiredBlockedAccountsJob::class)
                 ->hourly()
                 ->description('Bloquer automatiquement les comptes à la date programmée');

        // Débloquer automatiquement les comptes dont la date de fin de blocage est atteinte (toutes les heures en production)
        $schedule->job(\App\Jobs\UnarchiveExpiredBlockedAccountsJob::class)
                 ->hourly()
                 ->description('Débloquer automatiquement les comptes expirés');
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
