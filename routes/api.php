<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\RewardController;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetricsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Campaigns (public)
Route::prefix('campaigns')->group(function () {
    Route::get('/', [CampaignController::class, 'index'])->middleware('throttle:60,1');
    Route::get('/{id}', [CampaignController::class, 'show'])->middleware('throttle:60,1');
});

Route::get('/rewards', [RewardController::class, 'index']);
Route::get('/rewards/categories', [RewardController::class, 'categories']);
Route::get('/rewards/{id}', [RewardController::class, 'show']);

// Challenges (public)
Route::prefix('challenges')->group(function () {
    Route::get('/', [ChallengeController::class, 'index'])->middleware('throttle:60,1');
    Route::get('/{id}', [ChallengeController::class, 'show'])->middleware('throttle:60,1');
    Route::get('/{id}/submissions', [SubmissionController::class, 'indexByChallenge'])->middleware('throttle:60,1');
});

// Leaderboard
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->middleware('throttle:60,1');
Route::get('/leaderboard/weekly', [LeaderboardController::class, 'weekly'])->middleware('throttle:60,1');
Route::get('/leaderboard/custom', [LeaderboardController::class, 'custom'])->middleware('throttle:60,1');

// Rewards (lihat daftar hadiah)
Route::get('/rewards', [RewardController::class, 'index'])->middleware('throttle:60,1');

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/my/submissions', [SubmissionController::class, 'mine']);
    Route::get('/redemptions', [\App\Http\Controllers\RewardController::class, 'myRedemptions']);
    Route::post('/rewards/{id}/redeem', [\App\Http\Controllers\RewardController::class, 'redeem'])->middleware('isActive');

    // User submissions
    Route::post('/challenges/{id}/submissions', [SubmissionController::class, 'store']);
    /*
    |--------------------------------------------------------------------------
    | Admin-only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('isAdmin')->group(function () {

        // Submissions approval
        Route::get('/submissions', [SubmissionController::class, 'index']);
        Route::post('/submissions/{id}/approve', [SubmissionController::class, 'approve']);
        Route::post('/submissions/{id}/reject', [SubmissionController::class, 'reject']);

        // User
        Route::get('/users', [UserController::class, 'index']);

        Route::get('/metrics/summary', [MetricsController::class, 'summary']);
        Route::get('/metrics/activity-series', [MetricsController::class, 'activitySeries']);
        Route::get('/metrics/top-creators', [MetricsController::class, 'topCreators']);

        // Campaign management
        Route::post('/campaigns', [CampaignController::class, 'store']);
        Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
        Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
        Route::patch('/campaigns/{id}/status', [CampaignController::class, 'setStatus']);

        // Challenge management
        Route::post('/challenges', [ChallengeController::class, 'store']);
        Route::put('/challenges/{id}', [ChallengeController::class, 'update']);
        Route::delete('/challenges/{id}', [ChallengeController::class, 'destroy']);
        Route::patch('/challenges/{id}/status', [ChallengeController::class, 'setStatus']);

        // Reward management
        Route::post('/rewards', [RewardController::class, 'store']);
        Route::put('/rewards/{id}', [RewardController::class, 'update']);
        Route::delete('/rewards/{id}', [RewardController::class, 'destroy']);
    });


});
