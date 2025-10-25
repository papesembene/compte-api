<?php

namespace App\Contracts;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface pour le repository des transactions.
 *
 * Définit les méthodes pour gérer les transactions, permettant le découplage
 * et l'extensibilité (Open/Closed principle).
 */
interface TransactionRepositoryInterface
{
    /**
     * Récupère une liste paginée de transactions avec options de filtre.
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getTransactions(array $params): LengthAwarePaginator;

    /**
     * Crée une nouvelle transaction.
     *
     * @param array $data
     * @return Transaction
     */
    public function createTransaction(array $data): Transaction;

    /**
     * Met à jour une transaction.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction;

    /**
     * Supprime une transaction.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function deleteTransaction(Transaction $transaction): void;

    /**
     * Récupère les transactions du jour.
     *
     * @return Collection
     */
    public function getTodayTransactions(): Collection;

    /**
     * Récupère les transactions par compte.
     *
     * @param string $compteId
     * @return Collection
     */
    public function getTransactionsByCompte(string $compteId): Collection;

    /**
     * Calcule le solde d'un compte basé sur les transactions.
     *
     * @param string $compteId
     * @return float
     */
    public function calculateSolde(string $compteId): float;
}