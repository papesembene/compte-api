<?php

use App\Http\Controllers\Api\V1\AuthController;
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
Route::middleware(['throttle:api', 'throttle:1000,1,user', 'throttle:100,1,ip'])->prefix('v1')->group(function () {
    // Auth routes (non protégées)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Clients
    Route::middleware('auth:api')->group(function () {
        Route::get('clients', [ClientController::class, 'index']);
        Route::post('clients', [ClientController::class, 'store']);
        Route::get('clients/{client}', [ClientController::class, 'show']);
        Route::patch('clients/{client}', [ClientController::class, 'update']);
        Route::delete('clients/{client}', [ClientController::class, 'destroy']);
    });

    // Comptes
    // Liste des comptes (protégé par authentification)
    Route::middleware('auth:client')->group(function () {
        Route::get('comptes/non-archives', [CompteController::class, 'nonArchives']);
        Route::get('comptes/archives', [CompteController::class, 'archives'])->middleware('role:admin');
        Route::get('comptes', [CompteController::class, 'index']);
        Route::get('comptes/{compte}', [CompteController::class, 'show']);
    });

    // Routes admin seulement pour création/modification comptes
    Route::middleware(['auth:api', 'role:admin'])->group(function () {
        Route::post('comptes', [CompteController::class, 'store']);
        Route::patch('comptes/{compte}', [CompteController::class, 'update']);
        Route::delete('comptes/{compte}', [CompteController::class, 'destroy']);
        Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer']);
        Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer']);
    });

    // Transactions
    Route::middleware('auth:api')->group(function () {
        Route::post('transactions/depot/{compte}', [TransactionController::class, 'depot']);
        Route::post('transactions/retrait/{compte}', [TransactionController::class, 'retrait']);
        Route::get('transactions/historique/{compte}', [TransactionController::class, 'historique']);
        Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
    });
});
