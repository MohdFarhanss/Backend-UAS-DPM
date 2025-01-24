<?php

namespace App\Providers;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Route::middleware('api')->group(function () {
            Route::get('/login', function () {
                return response()->json(['message' => 'Unauthorized'], 401);
            })->name('login');


            // auth
            Route::post('register', [AuthController::class, 'register']);
            Route::post('/sign-in', [AuthController::class, 'signIn']);
            Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

            // categories
            Route::get('/categories', [CategoryController::class, 'index']);

            // products
            Route::get('/products', [ProductController::class, 'index']);
            Route::get('/products/{id}', [ProductController::class, 'show']);

            // favorites
            Route::post('/favorites/{productId}', [FavoriteController::class, 'favorite']);
            Route::post('/unfavorite/{productId}', [FavoriteController::class, 'unfavorite']);
            Route::get('/favorites', [FavoriteController::class, 'getFavorites']);

            // Transaction
            Route::post('/payment', [TransactionController::class, 'payment']);
            Route::get('/transaction-history', [TransactionController::class, 'history']);
        });
        Route::middleware('auth:sanctum')->get('/logout', [AuthController::class, 'logout']);

    }
}
