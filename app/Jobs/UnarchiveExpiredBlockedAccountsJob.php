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
        Log::info('Démarrage du déblocage automatique des comptes dont la date de fin de blocage est atteinte.');

        DB::transaction(function () {
            // Récupérer les comptes bloqués dont la date de fin de blocage est atteinte
            $comptesToUnblock = Compte::where('statut', 'bloque')
                ->whereNotNull('date_fin_blocage')
                ->where('date_fin_blocage', '<=', now())
                ->get();

            foreach ($comptesToUnblock as $compte) {
                // Supprimer l'archive de Neon
                DB::connection('neon')->table('blocked_accounts')
                    ->where('compte_id', $compte->id)
                    ->delete();

                // Débloquer le compte
                $compte->update(['statut' => 'debloque']);

                Log::info("Compte {$compte->id} ({$compte->numero_compte}) débloqué automatiquement et supprimé de l'archive Neon.");
            }

            Log::info("Déblocage automatique terminé : {$comptesToUnblock->count()} comptes débloqués et supprimés de l'archive.");
        });
    }
}
