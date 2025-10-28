<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Compte;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur pour gérer les transactions bancaires.
 *
 * Responsabilité : Gérer les opérations de dépôt et retrait.
 */
class TransactionController extends Controller
{
    use ApiResponseTrait;

    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Effectuer un dépôt sur un compte.
     */
    public function depot(StoreTransactionRequest $request, Compte $compte): JsonResponse
    {
        try {
            $transaction = $this->transactionService->effectuerDepot($compte, $request->validated());

            return $this->successResponse(
                [
                    'transaction' => $transaction->load('compte.client'),
                    'nouveau_solde' => $compte->fresh()->solde,
                ],
                'Dépôt effectué avec succès.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),  400);
        }
    }

    /**
     * Effectuer un retrait sur un compte.
     */
    public function retrait(StoreTransactionRequest $request, Compte $compte): JsonResponse
    {
        try {
            $transaction = $this->transactionService->effectuerRetrait($compte, $request->validated());

            return $this->successResponse(
                [
                    'transaction' => $transaction->load('compte.client'),
                    'nouveau_solde' => $compte->fresh()->solde,
                ],
                'Retrait effectué avec succès.',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }



    /**
     * Obtenir l'historique des transactions d'un compte.
     */
    // public function historique(Request $request, Compte $compte): JsonResponse
    // {
    //     try {
    //         $transactions = $this->transactionService->getHistoriqueTransactions($compte, $request->all());

    //         return $this->successResponse(
    //             [
    //                 'compte' => $compte->load('client'),
    //                 'transactions' => $transactions,
    //                 'solde_actuel' => $compte->solde,
    //             ],
    //             'Historique des transactions récupéré avec succès.'
    //         );
    //     } catch (\Exception $e) {
    //         return $this->errorResponse('Erreur lors de la récupération de l\'historique.',  500);
    //     }
    // }

    /**
     * Obtenir les détails d'une transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        try {
            $transaction = $this->transactionService->getTransactionDetails($transaction->id);

            return $this->successResponse(
                ['transaction' => $transaction],
                'Détails de la transaction récupérés avec succès.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Transaction non trouvée.', 404);
        }
    }
}
