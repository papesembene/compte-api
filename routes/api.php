<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes
Route::middleware('throttle:api')->prefix('v1')->group(function () {
    // Clients
    Route::apiResource('clients', ClientController::class);

    // Comptes
    Route::apiResource('comptes', CompteController::class);
    Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer']);
    Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer']);

    // Liste des comptes
    Route::get('comptes/non-archives', [CompteController::class, 'nonArchives']);
    Route::get('comptes/archives', [CompteController::class, 'archives']);

    // Transactions
    Route::post('transactions/depot/{compte}', [TransactionController::class, 'depot']);
    Route::post('transactions/retrait/{compte}', [TransactionController::class, 'retrait']);
    Route::post('transactions/virement', [TransactionController::class, 'virement']);
    Route::get('transactions/historique/{compte}', [TransactionController::class, 'historique']);
    Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
});
