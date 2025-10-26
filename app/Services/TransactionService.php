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
     * Effectue un dépôt sur un compte.
     */
    public function effectuerDepot(Compte $compte, array $data): Transaction
    {
        return DB::transaction(function () use ($compte, $data) {
            if ($compte->statut === 'bloque') {
                throw new \Exception('Impossible d\'effectuer un dépôt sur un compte bloqué');
            }

            return $this->transactionRepository->createTransaction([
                'compte_id' => $compte->id,
                'type' => 'depot',
                'montant' => $data['montant'],
                'date_transaction' => now(),
                'statut' => 'termine',
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    /**
     * Effectue un retrait sur un compte.
     */
    public function effectuerRetrait(Compte $compte, array $data): Transaction
    {
        return DB::transaction(function () use ($compte, $data) {
            if ($compte->statut === 'bloque') {
                throw new \Exception('Impossible d\'effectuer un retrait sur un compte bloqué');
            }

            $soldeActuel = $compte->solde;

            if ($soldeActuel < $data['montant']) {
                throw new \Exception('Solde insuffisant pour effectuer ce retrait');
            }

            return $this->transactionRepository->createTransaction([
                'compte_id' => $compte->id,
                'type' => 'retrait',
                'montant' => $data['montant'],
                'date_transaction' => now(),
                'statut' => 'termine',
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    /**
     * Effectue un virement entre comptes.
     */
    public function effectuerVirement(Compte $compteSource, Compte $compteDestination, array $data): array
    {
        return DB::transaction(function () use ($compteSource, $compteDestination, $data) {
            // Vérifier que les comptes ne sont pas bloqués
            if ($compteSource->statut === 'bloque') {
                throw new \Exception('Le compte source est bloqué');
            }

            if ($compteDestination->statut === 'bloque') {
                throw new \Exception('Le compte destination est bloqué');
            }

            $soldeSource = $compteSource->solde;

            if ($soldeSource < $data['montant']) {
                throw new \Exception('Solde insuffisant pour effectuer ce virement');
            }

            // Créer la transaction de débit
            $transactionDebit = $this->transactionRepository->createTransaction([
                'compte_id' => $compteSource->id,
                'type' => 'retrait',
                'montant' => $data['montant'],
                'date_transaction' => now(),
                'statut' => 'termine',
                'description' => $data['description'] ?? null,
            ]);

            // Créer la transaction de crédit
            $transactionCredit = $this->transactionRepository->createTransaction([
                'compte_id' => $compteDestination->id,
                'type' => 'depot',
                'montant' => $data['montant'],
                'date_transaction' => now(),
                'statut' => 'termine',
                'description' => $data['description'] ?? null,
            ]);

            return [
                'transaction_debit' => $transactionDebit,
                'transaction_credit' => $transactionCredit,
            ];
        });
    }

    /**
     * Récupère l'historique des transactions d'un compte.
     */
    public function getHistoriqueTransactions(Compte $compte, array $params = []): LengthAwarePaginator
    {
        $query = $compte->transactions()
            ->with('compte.client')
            ->orderBy('date_transaction', 'desc');

        return $query->paginate($params['limit'] ?? 20);
    }

    /**
     * Récupère les détails d'une transaction.
     */
    public function getTransactionDetails(string $transactionId): Transaction
    {
        return Transaction::with('compte.client')->findOrFail($transactionId);
    }
}
