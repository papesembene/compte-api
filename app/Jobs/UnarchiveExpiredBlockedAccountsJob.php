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

            Log::info("Nombre de comptes trouvés pour déblocage : {$comptesToUnblock->count()}");

            foreach ($comptesToUnblock as $compte) {
                Log::info("Traitement du compte {$compte->numero_compte} - Type: {$compte->type_compte} - Statut: {$compte->statut} - Date fin: {$compte->date_fin_blocage}");

                // Vérifier que c'est un compte épargne
                if ($compte->type_compte !== 'epargne') {
                    Log::warning("Tentative de déblocage automatique d'un compte non-épargne: {$compte->numero_compte}");
                    continue;
                }

                try {
                    // Vérifier si la table blocked_accounts existe dans Neon
                    $tableExists = DB::connection('neon')->select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'blocked_accounts')")[0]->exists ?? false;

                    if (!$tableExists) {
                        Log::warning("Table blocked_accounts n'existe pas dans Neon pour le déblocage du compte {$compte->numero_compte}. Skipping.");
                    } else {
                        // Supprimer l'archive de Neon
                        $deleted = DB::connection('neon')->table('blocked_accounts')
                            ->where('compte_id', $compte->id)
                            ->delete();

                        Log::info("Suppression de l'archive Neon pour le compte {$compte->numero_compte}: {$deleted} enregistrement(s) supprimé(s)");
                    }

                    // Débloquer le compte
                    $compte->update(['statut' => 'debloque']);

                    Log::info("✅ Compte {$compte->id} ({$compte->numero_compte}) débloqué automatiquement.");
                } catch (\Exception $e) {
                    Log::error("❌ Erreur lors du déblocage du compte {$compte->numero_compte}: " . $e->getMessage());
                    Log::error("Stack trace: " . $e->getTraceAsString());
                }
            }

            Log::info("Déblocage automatique terminé : {$comptesToUnblock->count()} comptes traités.");
        });
    }
}
