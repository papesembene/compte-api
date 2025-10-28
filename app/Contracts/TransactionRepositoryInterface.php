<?php

namespace App\Contracts;

/**
 * Interface pour les repositories de transactions.
 *
 * Définit les méthodes pour gérer les transactions, permettant le découplage
 * et l'extensibilité (ex. : Local, Cloud, etc.).
 */
interface TransactionRepositoryInterface
{
    /**
     * Récupère une liste paginée de transactions avec options de filtre et tri.
     *
     * @param array $params
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactions(array $params): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Crée une nouvelle transaction.
     *
     * @param array $data
     * @return \App\Models\Transaction
     */
    public function createTransaction(array $data): \App\Models\Transaction;

    /**
     * Met à jour une transaction.
     *
     * @param \App\Models\Transaction $transaction
     * @param array $data
     * @return \App\Models\Transaction
     */
    public function updateTransaction(\App\Models\Transaction $transaction, array $data): \App\Models\Transaction;

    /**
     * Supprime une transaction.
     *
     * @param \App\Models\Transaction $transaction
     * @return void
     */
    public function deleteTransaction(\App\Models\Transaction $transaction): void;

    /**
     * Récupère l'historique des transactions pour un compte.
     *
     * @param int $compteId
     * @param array $params
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getHistorique(int $compteId, array $params): \Illuminate\Pagination\LengthAwarePaginator;
}