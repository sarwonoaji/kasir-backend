<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductInController;
use App\Http\Controllers\ProductOutController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);

    // CRUD ADMIN (PAKAI ID)
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // POS SCAN BARCODE
    Route::get('/scan/{barcode}', [ProductController::class, 'scan']);

    // GENERATE BARCODE
    Route::get('/products/generate-barcode', [ProductController::class, 'generateBarcode']);
    
    Route::prefix('product-ins')->group(function () {
        Route::get('/', [ProductInController::class, 'index']);
        Route::post('/', [ProductInController::class, 'store']);
        Route::get('/{id}', [ProductInController::class, 'show']);
        Route::put('/{id}', [ProductInController::class, 'update']);
        Route::delete('/{id}', [ProductInController::class, 'destroy']);
        Route::get('/{id}/print', [ProductInController::class, 'print']);
    });

    Route::prefix('product-outs')->group(function () {
        Route::get('/', [ProductOutController::class, 'index']);
        Route::post('/', [ProductOutController::class, 'store']);
        Route::get('/{id}', [ProductOutController::class, 'show']);
        Route::delete('/{id}', [ProductOutController::class, 'destroy']);
    });
 });
    