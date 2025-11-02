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
        Log::info('Démarrage du blocage programmé des comptes.');

        DB::transaction(function () {
            // Bloquer les comptes dont la date de début de blocage est atteinte
            $comptesToBlock = Compte::where('statut', 'debloque')
                ->whereNotNull('date_debut_blocage')
                ->where('date_debut_blocage', '<=', now())
                ->get();

            Log::info("Nombre de comptes trouvés pour blocage : {$comptesToBlock->count()}");

            foreach ($comptesToBlock as $compte) {
                Log::info("Traitement du compte {$compte->numero_compte} - Type: {$compte->type_compte} - Statut: {$compte->statut} - Date debut: {$compte->date_debut_blocage}");

                // Vérifier que c'est un compte épargne
                if ($compte->type_compte !== 'epargne') {
                    Log::warning("Tentative de blocage automatique d'un compte non-épargne: {$compte->numero_compte}");
                    continue;
                }

                try {
                    // Vérifier si la table blocked_accounts existe dans Neon
                    $tableExists = DB::connection('neon')->select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'blocked_accounts')")[0]->exists ?? false;

                    if (!$tableExists) {
                        Log::warning("Table blocked_accounts n'existe pas dans Neon. Création de la table...");
                        DB::connection('neon')->statement("
                            CREATE TABLE IF NOT EXISTS blocked_accounts (
                                id UUID PRIMARY KEY,
                                compte_id UUID,
                                numero_compte VARCHAR(10),
                                type_compte VARCHAR(255),
                                client_id UUID,
                                date_debut_blocage TIMESTAMP,
                                date_fin_blocage TIMESTAMP NULL,
                                motif TEXT,
                                compte_data JSON,
                                client_data JSON,
                                archived_at TIMESTAMP,
                                created_at TIMESTAMP,
                                updated_at TIMESTAMP
                            )
                        ");
                        Log::info("Table blocked_accounts créée dans Neon.");
                    }

                    // Archiver le compte dans Neon avant de le bloquer
                    DB::connection('neon')->table('blocked_accounts')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'compte_id' => $compte->id,
                        'numero_compte' => $compte->numero_compte,
                        'type_compte' => $compte->type_compte,
                        'client_id' => $compte->client_id,
                        'date_debut_blocage' => $compte->date_debut_blocage,
                        'date_fin_blocage' => $compte->date_fin_blocage,
                        'motif' => 'Blocage automatique programmé',
                        'compte_data' => json_encode($compte->toArray()),
                        'client_data' => json_encode($compte->client->toArray()),
                        'archived_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $compte->update(['statut' => 'bloque']);
                    Log::info("✅ Compte {$compte->id} ({$compte->numero_compte}) bloqué automatiquement et archivé dans Neon.");
                } catch (\Exception $e) {
                    Log::error("❌ Erreur lors du blocage du compte {$compte->numero_compte}: " . $e->getMessage());
                    Log::error("Stack trace: " . $e->getTraceAsString());
                }
            }

            Log::info("Blocage programmé terminé : {$comptesToBlock->count()} comptes traités.");
        });
    }
}
