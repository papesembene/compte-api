<?php

namespace App\Observers;

use App\Events\TransactionValidated;
use App\Exceptions\CompteNotFoundException;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "creating" event.
     */
    public function creating(Transaction $transaction): void
    {
        Log::info("Vérification de la transaction {$transaction->id}");

        // Vérifier que le compte existe et n'est pas bloqué
        $compte = $transaction->compte;

        if (!$compte) {
            throw new CompteNotFoundException('Compte non trouvé pour la transaction');
        }

        if ($compte->statut === 'bloque') {
            throw new \Exception('Impossible d\'effectuer une transaction sur un compte bloqué');
        }

        // Pour les retraits, vérifier le solde
        if ($transaction->type === 'retrait') {
            $solde = $compte->solde; // Calculé dynamiquement

            if ($solde < $transaction->montant) {
                throw new \Exception('Solde insuffisant pour effectuer ce retrait');
            }
        }

        // Définir le statut par défaut
        if (empty($transaction->statut)) {
            $transaction->statut = 'en_attente';
        }
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        Log::info("Transaction {$transaction->id} créée, déclenchement de l'event");

        // Marquer comme terminée si c'est un dépôt ou si le solde est suffisant
        if ($transaction->type === 'depot' || $transaction->statut === 'en_attente') {
            $transaction->update(['statut' => 'termine']);
        }

        // Déclencher l'event
        event(new TransactionValidated($transaction));
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
