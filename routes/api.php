<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // Product routes (all protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products/{product}/thumbnail', [ProductController::class, 'uploadThumbnail']);
        Route::apiResource('products', ProductController::class);
    });
});
