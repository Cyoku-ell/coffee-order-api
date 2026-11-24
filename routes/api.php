<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OwnerOrderController;

// Register
Route::post('/register', [RegisteredUserController::class, 'store']);

// Login
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Rute yang membutuhkan login
Route::middleware('auth:sanctum')->group(function () {

    // Menu: Owner dan User bisa melihat
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{menu}', [MenuController::class, 'show']);

    // Menu: Hanya Owner yang bisa mengelola
    Route::middleware('role:owner')->group(function () {
        Route::post('/menus', [MenuController::class, 'store']);
        Route::put('/menus/{menu}', [MenuController::class, 'update']);
        Route::delete('/menus/{menu}', [MenuController::class, 'destroy']);
        Route::get('/owner/orders', [OwnerOrderController::class, 'index']);
        Route::get('/owner/orders/{order}', [OwnerOrderController::class, 'show']);
        Route::patch('/owner/orders/{order}/status', [OwnerOrderController::class, 'updateStatus']);
    });

    // Cart: Hanya User yang bisa mengelola keranjang
    Route::middleware('role:user')->group(function () {
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'store']);
        Route::put('/cart/items/{cartItem}', [CartController::class, 'update']);
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders/checkout', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(),
        ]);
    });

    Route::middleware('role:owner')->get('/owner-test', function () {
        return response()->json([
            'message' => 'Welcome Owner!',
        ]);
    });

    Route::middleware('role:user')->get('/user-test', function () {
        return response()->json([
            'message' => 'Welcome User!',
        ]);
    });
});
