<?php

use App\Http\Controllers\OutdoorMonitoringController;
use App\Http\Controllers\IndoorMonitoringController;
use App\Http\Controllers\IntegrityStatsController;
use Illuminate\Support\Facades\Route;

Route::middleware('verifyApiKey')->group(function () {
    Route::post('/store-data-outdoor-monitor', [OutdoorMonitoringController::class, 'storeData']);
    Route::post('/store-data-indoor-monitor',  [IndoorMonitoringController::class,  'storeData']);
    Route::post('/store-integrity-stats',      [IntegrityStatsController::class,    'storeData']);
});
