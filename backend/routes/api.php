<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\LeetCodeController;  
use Illuminate\Support\Facades\Route;

// 1. Authentication Routes (Prefix: /api/auth/*)
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class);
        Route::get('/user', UserController::class);
    });
});

// 2. Core Application Routes (Prefix: /api/*)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    
    // --- LEETCODE ROUTES ---
    Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index']);
    Route::post('/user/connect-leetcode', [LeetCodeController::class, 'connect']); 
    Route::get('/user/leetcode-calendar', [LeetCodeController::class, 'getCalendar']);
    Route::get('/user/leetcode-performance', [LeetCodeController::class, 'getPerformance']);
});