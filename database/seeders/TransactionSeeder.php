<?php

namespace Database\Seeders;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les comptes actifs (non bloqués)
        $comptes = Compte::where('statut', 'debloque')->get();

        if ($comptes->isEmpty()) {
            $this->command->info('Aucun compte actif trouvé. Veuillez exécuter le CompteSeeder d\'abord et vous assurer qu\'il y a des comptes débloqués.');
            return;
        }

        // Créer des transactions pour chaque compte actif
        $comptes->each(function ($compte) {
            // Nombre aléatoire de transactions par compte (1 à 5)
            $nombreTransactions = rand(1, 5);

            for ($i = 0; $i < $nombreTransactions; $i++) {
                // Alterner entre dépôt et retrait
                $type = rand(0, 1) ? 'depot' : 'retrait';

                // Montant aléatoire
                $montant = rand(100, 10000);

                // Vérifier le solde pour les retraits
                if ($type === 'retrait') {
                    $soldeActuel = $compte->solde;
                    if ($soldeActuel < $montant) {
                        // Si solde insuffisant, faire un dépôt au lieu d'un retrait
                        $type = 'depot';
                    }
                }

                Transaction::factory()->create([
                    'compte_id' => $compte->id,
                    'type' => $type,
                    'montant' => $montant,
                    'statut' => 'termine',
                    'description' => $type === 'depot' ? 'Dépôt via seeder' : 'Retrait via seeder',
                ]);
            }
        });

        $this->command->info('TransactionSeeder exécuté avec succès. ' . Transaction::count() . ' transactions créées.');
    }
}
