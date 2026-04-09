<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {

    // ── Public API ─────────────────────────────────────────────────────────────
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    // ── Authenticated API ──────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);

        // Documents
        Route::apiResource('documents', DocumentController::class);
        Route::get('documents/{id}/download', [DocumentController::class, 'download']);
    });
});
