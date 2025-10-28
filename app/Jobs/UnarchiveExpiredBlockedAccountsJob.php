<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Repositories\CloudTransactionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job pour désarchiver les comptes bloqués dont la date de fin de blocage est échue.
 *
 * Responsabilité : Restaurer automatiquement les comptes bloqués après expiration de leur période de blocage.
 */
class UnarchiveExpiredBlockedAccountsJob implements ShouldQueue
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
        Log::info('Démarrage de la désarchivage des comptes bloqués dont la date de fin de blocage est échue.');

        DB::transaction(function () {
            // Récupérer les comptes soft deleted (archivés) dont la date de fin de blocage est échue
            $comptesToUnarchive = Compte::onlyTrashed()
                ->whereNotNull('date_fin_blocage')
                ->where('date_fin_blocage', '<=', now())
                ->get();

            foreach ($comptesToUnarchive as $compte) {
                // Restaurer le compte (annuler le soft delete)
                $compte->restore();

                // Restaurer le statut à débloqué
                $compte->update(['statut' => 'debloque']);

                Log::info("Compte {$compte->id} ({$compte->numero_compte}) restauré automatiquement.");
            }

            Log::info("Désarchivage terminé : {$comptesToUnarchive->count()} comptes restaurés.");
        });
    }
}
