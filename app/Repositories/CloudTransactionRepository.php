<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Repository pour les transactions en base cloud (Neon PostgreSQL).
 *
 * Implémente TransactionRepositoryInterface pour la DB d'archivage.
 */
class CloudTransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Récupère une liste paginée de transactions avec options de filtre.
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getTransactions(array $params): LengthAwarePaginator
    {
        $query = Transaction::on('neon')->query(); // Utilise la connexion 'neon'

        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }

        if (isset($params['statut'])) {
            $query->where('statut', $params['statut']);
        }

        if (isset($params['compte_id'])) {
            $query->where('compte_id', $params['compte_id']);
        }

        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        return $query->orderBy($sort, $order)->paginate($params['limit'] ?? 10);
    }

    /**
     * Crée une nouvelle transaction.
     *
     * @param array $data
     * @return Transaction
     */
    public function createTransaction(array $data): Transaction
    {
        return Transaction::on('neon')->create($data);
    }

    /**
     * Met à jour une transaction.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction;
    }

    /**
     * Supprime une transaction.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function deleteTransaction(Transaction $transaction): void
    {
        $transaction->delete();
    }

    /**
     * Récupère les transactions du jour.
     *
     * @return Collection
     */
    public function getTodayTransactions(): Collection
    {
        return Transaction::on('neon')->duJour()->get();
    }

    /**
     * Récupère les transactions par compte.
     *
     * @param string $compteId
     * @return Collection
     */
    public function getTransactionsByCompte(string $compteId): Collection
    {
        return Transaction::on('neon')->where('compte_id', $compteId)->get();
    }

    /**
     * Calcule le solde d'un compte basé sur les transactions.
     *
     * @param string $compteId
     * @return float
     */
    public function calculateSolde(string $compteId): float
    {
        return Transaction::on('neon')->where('compte_id', $compteId)
            ->where('statut', 'termine')
            ->sum(DB::raw("CASE WHEN type = 'depot' THEN montant ELSE -montant END"));
    }

    /**
     * Récupère l'historique des transactions pour un compte.
     *
     * @param int $compteId
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getHistorique(int $compteId, array $params): LengthAwarePaginator
    {
        $query = Transaction::on('neon')->where('compte_id', $compteId);

        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }

        if (isset($params['statut'])) {
            $query->where('statut', $params['statut']);
        }

        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        return $query->orderBy($sort, $order)->paginate($params['limit'] ?? 10);
    }
}