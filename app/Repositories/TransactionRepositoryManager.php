<?php

namespace App\Repositories;

use App\Contracts\TransactionRepositoryInterface;
use Carbon\Carbon;

/**
 * Manager pour router les requêtes de transactions vers le repository approprié.
 *
 * Implémente le pattern Strategy pour choisir dynamiquement entre Local et Cloud
 * basé sur la date (aujourd'hui → local, sinon → cloud pour archivage).
 * Respecte Open/Closed principle : extensible sans modification.
 */
class TransactionRepositoryManager
{
    private LocalTransactionRepository $localRepository;
    private CloudTransactionRepository $cloudRepository;

    public function __construct(
        LocalTransactionRepository $localRepository,
        CloudTransactionRepository $cloudRepository
    ) {
        $this->localRepository = $localRepository;
        $this->cloudRepository = $cloudRepository;
    }

    /**
     * Route vers le repository approprié basé sur la date.
     *
     * - Transactions du jour : Local (Render DB)
     * - Transactions plus anciennes : Cloud (Neon DB pour archivage)
     *
     * @param \DateTime|null $date
     * @return TransactionRepositoryInterface
     */
    public function getRepository(?\DateTime $date = null): TransactionRepositoryInterface
    {
        $targetDate = $date ? Carbon::instance($date) : now();

        if ($targetDate->isToday()) {
            return $this->localRepository;
        }

        return $this->cloudRepository;
    }

    /**
     * Récupère une liste paginée de transactions avec routing automatique.
     *
     * @param array $params
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTransactions(array $params): \Illuminate\Pagination\LengthAwarePaginator
    {
        $repository = $this->getRepository();

        if (isset($params['date']) && !Carbon::parse($params['date'])->isToday()) {
            $repository = $this->cloudRepository;
        }

        return $repository->getTransactions($params);
    }

    /**
     * Crée une transaction avec routing automatique.
     *
     * @param array $data
     * @return \App\Models\Transaction
     */
    public function createTransaction(array $data): \App\Models\Transaction
    {
        // Les nouvelles transactions vont toujours en local
        return $this->localRepository->createTransaction($data);
    }

    /**
     * Met à jour une transaction.
     *
     * @param \App\Models\Transaction $transaction
     * @param array $data
     * @return \App\Models\Transaction
     */
    public function updateTransaction(\App\Models\Transaction $transaction, array $data): \App\Models\Transaction
    {
        $repository = $this->getRepository($transaction->date_transaction);

        return $repository->updateTransaction($transaction, $data);
    }

    /**
     * Supprime une transaction.
     *
     * @param \App\Models\Transaction $transaction
     * @return void
     */
    public function deleteTransaction(\App\Models\Transaction $transaction): void
    {
        $repository = $this->getRepository($transaction->date_transaction);

        $repository->deleteTransaction($transaction);
    }

    /**
     * Récupère les transactions du jour.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTodayTransactions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->localRepository->getTodayTransactions();
    }

    /**
     * Récupère les transactions par compte avec routing.
     *
     * @param string $compteId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactionsByCompte(string $compteId): \Illuminate\Database\Eloquent\Collection
    {
        // Pour simplifier, on récupère de local d'abord, puis cloud si nécessaire
        $local = $this->localRepository->getTransactionsByCompte($compteId);
        $cloud = $this->cloudRepository->getTransactionsByCompte($compteId);

        return $local->merge($cloud);
    }

    /**
     * Calcule le solde d'un compte avec routing.
     *
     * @param string $compteId
     * @return float
     */
    public function calculateSolde(string $compteId): float
    {
        $localSolde = $this->localRepository->calculateSolde($compteId);
        $cloudSolde = $this->cloudRepository->calculateSolde($compteId);

        return $localSolde + $cloudSolde;
    }
}