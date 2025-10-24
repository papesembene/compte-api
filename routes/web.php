<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwaggerController;
use App\Http\Controllers\SwaggerAssetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Swagger documentation routes
Route::get('/api/documentation', [SwaggerController::class, 'api'])->middleware('web');
Route::get('/docs', function () {
    return file_get_contents(public_path('swagger-ui/index.html'));
})->middleware('web');
Route::get('/swagger-ui/{asset}', [SwaggerAssetController::class, 'index'])->where('asset', '.*');
