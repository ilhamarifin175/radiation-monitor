<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OutdoorMonitoringController;
use App\Http\Controllers\IndoorMonitoringController;
use App\Http\Controllers\IntegrityStatsController;
use Illuminate\Support\Facades\Route;

// ===== Halaman =====
Route::get('/',                         [DashboardController::class,       'index']);
Route::get('/outdoor-monitor',          [OutdoorMonitoringController::class, 'index']);
Route::get('/indoor-monitor',           [IndoorMonitoringController::class,  'index']);
Route::get('/outdoor-monitor-data-tables', [OutdoorMonitoringController::class, 'tables']);
Route::get('/indoor-monitor-data-tables',  [IndoorMonitoringController::class,  'tables']);
Route::get('/integrity-data-tables',    [IntegrityStatsController::class,    'tables']);

// ===== Data (AJAX) =====
Route::middleware('verifyApiKey')->group(function () {
    Route::get('/latest-outdoor-cps',  [OutdoorMonitoringController::class, 'getLatestData']);
    Route::get('/latest-indoor-cps',   [IndoorMonitoringController::class,  'getLatestData']);
    Route::get('/outdoor-history',     [OutdoorMonitoringController::class, 'getDataHistory']);
    Route::get('/indoor-history',      [IndoorMonitoringController::class,  'getDataHistory']);
});
