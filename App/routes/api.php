<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutePlannerController;

Route::middleware('api')->group(function () {
    Route::get('/routes/search', [RoutePlannerController::class, 'search']);
    Route::get('/routes/{routeId}', [RoutePlannerController::class, 'show']);
    Route::get('/routes/{routeId}/stops', [RoutePlannerController::class, 'stops']);
    Route::get('/routes/{routeId}/stops/{stopId}/arrivals', [RoutePlannerController::class, 'arrivals']);
    Route::get('/routes/{routeId}/vehicles/live', [RoutePlannerController::class, 'livePositions']);
    Route::get('/routes/{routeId}/notices', [RoutePlannerController::class, 'notices']);
});
