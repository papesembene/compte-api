<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job pour synchroniser le statut de blocage des comptes entre local et Neon.
 *
 * Responsabilité : Maintenir la cohérence du statut des comptes entre les deux DB.
 */
class SyncBlockedAccountsJob implements ShouldQueue
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
        Log::info('Démarrage de la synchronisation des statuts de blocage des comptes.');

        DB::transaction(function () {
            // Récupérer tous les comptes locaux
            $localComptes = Compte::all();

            foreach ($localComptes as $localCompte) {
                // Vérifier le statut en Neon
                $neonCompte = Compte::on('neon')->find($localCompte->id);

                if ($neonCompte) {
                    // Si les statuts diffèrent, synchroniser vers local
                    if ($neonCompte->statut !== $localCompte->statut) {
                        $localCompte->update(['statut' => $neonCompte->statut]);
                        Log::info("Statut du compte {$localCompte->id} synchronisé : {$neonCompte->statut}");
                    }
                } else {
                    // Si le compte n'existe pas en Neon, l'archiver
                    Compte::on('neon')->create($localCompte->toArray());
                    Log::info("Compte {$localCompte->id} archivé vers Neon.");
                }
            }

            // Récupérer les comptes en Neon et synchroniser vers local si nécessaire
            $neonComptes = Compte::on('neon')->all();

            foreach ($neonComptes as $neonCompte) {
                $localCompte = Compte::find($neonCompte->id);

                if ($localCompte && $neonCompte->statut !== $localCompte->statut) {
                    $localCompte->update(['statut' => $neonCompte->statut]);
                    Log::info("Statut du compte {$neonCompte->id} synchronisé vers local : {$neonCompte->statut}");
                }
            }

            Log::info('Synchronisation des statuts de blocage terminée.');
        });
    }
}
