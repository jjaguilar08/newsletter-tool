<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CampaignAssetController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\Testing\E2EUserController;
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

Route::post('/marketing/sample-newsletter', [MarketingController::class, 'sampleNewsletter'])
    ->middleware('throttle:sample-newsletter')
    ->name('marketing.sample-newsletter');

Route::post('/subscribers/import', [SubscriberController::class, 'import'])
    ->middleware('auth:sanctum')
    ->name('subscribers.import');

Route::apiResource('subscribers', SubscriberController::class)
    ->middleware('auth:sanctum');

Route::get('/campaigns/default-template', [CampaignController::class, 'defaultTemplate'])
    ->middleware('auth:sanctum')
    ->name('campaigns.default-template');

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

// Unattended-safe user create/delete for the frontend's Playwright E2E
// suite - see E2EUserController's own doc comment for why this is the one
// exception to "no registration flow." Gated to non-production by
// 'testing.only' (see RestrictToNonProduction), deliberately unauthenticated
// otherwise since it exists to create the very session a spec logs in with.
Route::post('/testing/users', [E2EUserController::class, 'store'])
    ->middleware('testing.only')
    ->name('testing.users.store');

Route::delete('/testing/users/{user}', [E2EUserController::class, 'destroy'])
    ->middleware('testing.only')
    ->name('testing.users.destroy');
