<?php

use App\Http\Controllers\Api\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\CustomerAuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;

Route::post('/register',[CustomerAuthController::class , 'register']);
Route::post('/login', [CustomerAuthController::class , 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/me', [CustomerAuthController::class, 'me']);
    Route::post('/update-profile', [CustomerAuthController::class, 'updateProfile']);
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']);
    Route::post('/update-profile', [AdminAuthController::class, 'updateProfile']);
})->middleware(['auth:sanctum', 'isAdmin']);

Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('/product/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {
    Route::post('/product', [\App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::put('/product/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('/product/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::get('/cart/{id}', [CartController::class, 'show']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/orders', [CheckoutController::class, 'orderHistory']);
    Route::get('/order/{orderId}', [CheckoutController::class, 'orderDetails']);
});


