<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DriverRideController;
use App\Http\Controllers\Api\V1\GroupChatController;
use App\Http\Controllers\Api\V1\RideOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware(['token.not_expired', 'auth:sanctum'])->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware(['token.not_expired', 'auth:sanctum', 'role:admin'])->get('/admin/panel', function () {
        return response()->json(['message' => 'Admin panel access granted']);
    });

    Route::middleware(['token.not_expired', 'auth:sanctum', 'role:driver,admin'])->group(function (): void {
        Route::get('/driver/queue', function () {
            return response()->json(['message' => 'Driver queue access granted']);
        });
        Route::get('/driver/available-rides', [DriverRideController::class, 'availableRides']);
        Route::get('/driver/my-rides', [DriverRideController::class, 'myRides']);
        Route::get('/driver/my-rides/{rideOrder}', [DriverRideController::class, 'showMyRide']);
    });

    Route::middleware(['token.not_expired', 'auth:sanctum', 'role:rider'])->group(function (): void {
        Route::post('/ride-orders', [RideOrderController::class, 'store']);
        Route::get('/ride-orders', [RideOrderController::class, 'index']);
    });

    Route::middleware(['token.not_expired', 'auth:sanctum'])->patch('/ride-orders/{rideOrder}/transition', [RideOrderController::class, 'transition']);
    Route::middleware(['token.not_expired', 'auth:sanctum'])->get('/ride-orders/{rideOrder}', [RideOrderController::class, 'show']);

    Route::middleware(['token.not_expired', 'auth:sanctum'])->group(function (): void {
        Route::get('/ride-orders/{rideOrder}/chat', [GroupChatController::class, 'showByRide']);
        Route::post('/group-chats/{chat}/messages', [GroupChatController::class, 'sendMessage']);
        Route::get('/group-chats/{chat}/messages', [GroupChatController::class, 'getMessages']);
        Route::post('/group-chats/{chat}/read', [GroupChatController::class, 'markRead']);
        Route::patch('/group-chats/{chat}/dnd', [GroupChatController::class, 'updateDnd']);
    });
});
