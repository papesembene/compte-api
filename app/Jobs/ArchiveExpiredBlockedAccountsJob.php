<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Models\Transaction;
use App\Repositories\CloudTransactionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job pour archiver les comptes bloqués dont la date de début de blocage est échue.
 *
 * Responsabilité : Archiver automatiquement les comptes bloqués après expiration de leur période de blocage.
 */
class ArchiveExpiredBlockedAccountsJob implements ShouldQueue
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
        Log::info('Démarrage de l\'archivage des comptes bloqués dont la date de début de blocage est échue.');

        DB::transaction(function () {
            // Récupérer les comptes bloqués dont la date de début de blocage est échue
            $comptesToArchive = Compte::where('statut', 'bloque')
                ->whereNotNull('date_debut_blocage')
                ->where('date_debut_blocage', '<=', now())
                ->get();

            foreach ($comptesToArchive as $compte) {
                // Supprimer le compte de la DB locale (soft delete)
                $compte->delete();

                Log::info("Compte {$compte->id} ({$compte->numero_compte}) archivé automatiquement.");
            }

            Log::info("Archivage terminé : {$comptesToArchive->count()} comptes archivés.");
        });
    }
}
