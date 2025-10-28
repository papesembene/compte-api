<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;
use App\Repositories\TransactionRepositoryManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service pour la gestion des transactions.
 *
 * Responsabilité : Encapsuler la logique métier des transactions.
 */
class TransactionService
{
    private TransactionRepositoryManager $transactionRepository;

    public function __construct(TransactionRepositoryManager $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Effectuer un dépôt sur un compte.
     */
    public function effectuerDepot(Compte $compte, array $data): Transaction
    {
        return DB::transaction(function () use ($compte, $data) {
            // Vérifier que le compte n'est pas bloqué
            if ($compte->statut === 'bloque') {
                throw new \Exception('Impossible d\'effectuer un dépôt sur un compte bloqué.');
            }

            // Créer la transaction
            $transaction = $this->transactionRepository->createTransaction([
                'compte_id' => $compte->id,
                'type' => 'depot',
                'montant' => $data['montant'],
                'description' => $data['description'] ?? 'Dépôt',
                'statut' => 'termine',
            ]);

            return $transaction;
        });
    }

    /**
     * Effectuer un retrait sur un compte.
     */
    public function effectuerRetrait(Compte $compte, array $data): Transaction
    {
        return DB::transaction(function () use ($compte, $data) {
            // Vérifier que le compte n'est pas bloqué
            if ($compte->statut === 'bloque') {
                throw new \Exception('Impossible d\'effectuer un retrait sur un compte bloqué.');
            }

            // Vérifier le solde
            if ($compte->solde < $data['montant']) {
                throw new \Exception('Solde insuffisant pour effectuer ce retrait.');
            }

            // Créer la transaction
            $transaction = $this->transactionRepository->createTransaction([
                'compte_id' => $compte->id,
                'type' => 'retrait',
                'montant' => $data['montant'],
                'description' => $data['description'] ?? 'Retrait',
                'statut' => 'termine',
            ]);

            return $transaction;
        });
    }

    /**
     * Obtenir l'historique des transactions d'un compte.
     */
    // public function getHistoriqueTransactions(Compte $compte, array $params = []): LengthAwarePaginator
    // {
    //     return $this->transactionRepository->getHistorique($compte->id, $params);
    // }

    /**
     * Obtenir les détails d'une transaction.
     */
    public function getTransactionDetails(int $transactionId): Transaction
    {
        $transaction = Transaction::with('compte.client')->find($transactionId);

        if (!$transaction) {
            throw new \Exception('Transaction non trouvée.');
        }

        return $transaction;
    }
}