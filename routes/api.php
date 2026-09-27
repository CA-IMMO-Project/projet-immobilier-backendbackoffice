<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\SearchRequestController;
use App\Http\Controllers\Api\TerrainController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées (nécessitent un token Sanctum valide)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/restore', [UserController::class, 'restore']);
    
    Route::apiResource('clients', ClientController::class);
    Route::post('clients/{id}/restore', [ClientController::class, 'restore']);

    Route::apiResource('search-requests', SearchRequestController::class);

    Route::apiResource('terrains', TerrainController::class);
    Route::post('terrains/{id}/restore', [TerrainController::class, 'restore']);
    Route::post('terrains/{terrain}/acheter', [TerrainController::class, 'acheter']);

    Route::apiResource('purchase-requests', PurchaseRequestController::class);

    Route::apiResource('visits', VisitController::class);

    Route::apiResource('reservations', ReservationController::class);
});