<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\RewardController;

// Campaigns (public view)
Route::prefix('campaigns')->group(function () {
    Route::get('/',     [CampaignController::class, 'index'])->middleware('throttle:60,1');
    Route::get('/{id}', [CampaignController::class, 'show'])->middleware('throttle:60,1');
});

// Leaderboard (public view)
Route::get('/leaderboard',        [LeaderboardController::class, 'index'])->middleware('throttle:60,1');
Route::get('/leaderboard/weekly', [LeaderboardController::class, 'weekly'])->middleware('throttle:60,1');
Route::get('/leaderboard/custom', [LeaderboardController::class, 'custom'])->middleware('throttle:60,1');

// Rewards 
Route::get('/rewards', [RewardController::class, 'index'])->middleware('throttle:60,1');

// Auth (Sanctum)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Submissions 
    Route::prefix('challenges')->group(function () {
        Route::post('/{id}/submissions', [SubmissionController::class, 'store']);
        Route::get('/{id}/submissions',  [SubmissionController::class, 'indexByChallenge']); // optional: buat moderator/user
    });

    // Rewards
    Route::post('/rewards/{id}/redeem', [RewardController::class, 'redeem']);
    Route::get('/redemptions',          [RewardController::class, 'myRedemptions']);

    // Admin-only routes
    Route::middleware('isAdmin')->group(function () {
        Route::post('/submissions/{id}/approve', [SubmissionController::class, 'approve']);
    });
});
