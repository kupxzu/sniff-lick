<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()
    ]);
});

// Authentication Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
    });
});

// User Profile Routes (Protected)
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
    Route::put('username', [UserController::class, 'updateUsername'])->name('user.update-username');
    Route::put('email', [UserController::class, 'updateEmail'])->name('user.update-email');
    Route::put('password', [UserController::class, 'updatePassword'])->name('user.update-password');
});

// Pet Routes (Protected)
Route::middleware('auth:sanctum')->apiResource('pets', PetController::class);

// Admin Client Management Routes (Admin Only)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('clients', [ClientController::class, 'index'])->name('admin.clients.index');
    Route::get('clients/{id}', [ClientController::class, 'show'])->name('admin.clients.show');
    Route::get('clients-with-pets', [ClientController::class, 'clientsWithPets'])->name('admin.clients-with-pets');
    Route::get('clients/{clientId}/pets', [ClientController::class, 'clientPets'])->name('admin.client-pets');
    Route::get('dashboard', [ClientController::class, 'dashboard'])->name('admin.dashboard');
});

// Legacy route for compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'user' => $request->user()
    ]);
})->name('user.legacy');
