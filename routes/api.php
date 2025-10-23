<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompteController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

// API v1 Routes
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Clients
    Route::apiResource('clients', ClientController::class);

    // Comptes
    Route::apiResource('comptes', CompteController::class);
    Route::post('comptes/{compte}/bloquer', [CompteController::class, 'bloquer']);
    Route::post('comptes/{compte}/debloquer', [CompteController::class, 'debloquer']);
});

// Temporary route for setting up database
Route::get('/setup-database', function () {
    try {
        Artisan::call('migrate');
        Artisan::call('db:seed');
        return response()->json(['message' => 'Migrations and seeders executed successfully']);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
