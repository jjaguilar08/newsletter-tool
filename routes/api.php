<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CampaignAssetController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\UnsubscribeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'role:staff']);

Route::get('/unsubscribe/{token}', UnsubscribeController::class)
    ->name('unsubscribe');

Route::post('/subscribers/import', [SubscriberController::class, 'import'])
    ->middleware('auth:sanctum')
    ->name('subscribers.import');

Route::apiResource('subscribers', SubscriberController::class)
    ->middleware('auth:sanctum');

Route::apiResource('campaigns', CampaignController::class)
    ->middleware('auth:sanctum');

Route::post('/campaigns/{campaign}/send', [CampaignController::class, 'send'])
    ->middleware('auth:sanctum')
    ->name('campaigns.send');

Route::post('/campaigns/{campaign}/schedule', [CampaignController::class, 'schedule'])
    ->middleware('auth:sanctum')
    ->name('campaigns.schedule');

Route::get('/campaigns/{campaign}/preview', [CampaignController::class, 'preview'])
    ->middleware('auth:sanctum')
    ->name('campaigns.preview');

Route::get('/campaigns/{campaign}/sends', [CampaignController::class, 'sends'])
    ->middleware('auth:sanctum')
    ->name('campaigns.sends');

Route::post('/campaigns/assets', [CampaignAssetController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('campaigns.assets.store');

Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
    ->middleware(['auth:sanctum', 'role:staff'])
    ->name('dashboard.stats');
