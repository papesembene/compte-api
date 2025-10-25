<?php

namespace Database\Seeders;

use App\Models\Compte;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des comptes avec des transactions pour tester le calcul du solde
        Compte::factory(20)->create()->each(function ($compte) {
            // Ajouter quelques transactions pour calculer le solde
            $montantDepot = rand(1000, 10000);
            \App\Models\Transaction::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'compte_id' => $compte->id,
                'type' => 'depot',
                'montant' => $montantDepot,
                'date_transaction' => now()->subDays(rand(0, 30)),
                'statut' => 'termine',
            ]);

            // Ajouter quelques retraits (optionnel)
            if (rand(0, 1)) {
                $montantRetrait = rand(100, $montantDepot / 2);
                \App\Models\Transaction::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'compte_id' => $compte->id,
                    'type' => 'retrait',
                    'montant' => $montantRetrait,
                    'date_transaction' => now()->subDays(rand(0, 15)),
                    'statut' => 'termine',
                ]);
            }
        });
    }
}
