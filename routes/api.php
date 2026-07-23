<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
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
