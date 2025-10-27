<?php

namespace App\Jobs;

use App\Jobs\ArchiveBlockedAccountsJob;
use App\Jobs\UnarchiveExpiredBlockedAccountsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job de test pour déclencher manuellement les jobs d'archivage.
 *
 * Responsabilité : Permettre de tester les jobs d'archivage et désarchivage.
 */
class TestArchiveJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Déclenchement manuel des jobs d\'archivage pour test.');

        // Déclencher l'archivage des comptes bloqués expirés
        ArchiveBlockedAccountsJob::dispatch();

        // Déclencher le désarchivage des comptes expirés
        UnarchiveExpiredBlockedAccountsJob::dispatch();

        Log::info('Jobs d\'archivage déclenchés avec succès.');
    }
}
