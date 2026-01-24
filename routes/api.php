<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductInController;
use App\Http\Controllers\ProductOutController;
use App\Http\Controllers\Api\CashierSessionController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });

        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::get('/products/generate-barcode', [ProductController::class, 'generateBarcode']);

        Route::prefix('product-ins')->group(function () {
            Route::get('/', [ProductInController::class, 'index']);
            Route::post('/', [ProductInController::class, 'store']);
            Route::get('/{id}', [ProductInController::class, 'show']);
            Route::put('/{id}', [ProductInController::class, 'update']);
            Route::delete('/{id}', [ProductInController::class, 'destroy']);
            Route::get('/{id}/print', [ProductInController::class, 'print']);
        });

        Route::prefix('shifts')->group(function () {
            Route::get('/', [ShiftController::class, 'index']);
            Route::post('/', [ShiftController::class, 'store']);
            Route::get('/{id}', [ShiftController::class, 'show']);
            Route::put('/{id}', [ShiftController::class, 'update']);
            Route::delete('/{id}', [ShiftController::class, 'destroy']);
        });

        Route::get('/scan/{barcode}', [ProductController::class, 'scan']);

        Route::prefix('product-outs')->group(function () {
            Route::get('/', [ProductOutController::class, 'index']);
            Route::post('/', [ProductOutController::class, 'store']);
            Route::get('/{id}', [ProductOutController::class, 'show']);
            Route::delete('/{id}', [ProductOutController::class, 'destroy']);
        });

        Route::post('/cashier-sessions/open', [CashierSessionController::class, 'open']);
        Route::post('/cashier-sessions/{id}/close', [CashierSessionController::class, 'close']);
        Route::get('/cashier-sessions/active', [CashierSessionController::class, 'activeSession']);
        Route::get('/cashier-sessions/history', [CashierSessionController::class, 'history']);
    });
    