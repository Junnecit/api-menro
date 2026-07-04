<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PlantingMonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestItemController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('google/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('google/callback', [GoogleAuthController::class, 'callback']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::delete('delete-account', [AuthController::class, 'deleteAccount']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::match(['put', 'patch'], 'profile/update', [ProfileController::class, 'update']);
    Route::post('profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::delete('profile/photo', [ProfileController::class, 'removePhoto']);
    Route::get('users/options', [UserController::class, 'options']);

    Route::middleware('role:super-admin')->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('users/trash', [UserController::class, 'trash']);
        Route::post('users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('users/{id}/force', [UserController::class, 'forceDestroy']);
        Route::apiResource('users', UserController::class);
    });

    Route::apiResource('test-items', TestItemController::class);

    Route::get('locations/barangays', [RequestController::class, 'barangays']);
    Route::get('agencies/trash', [AgencyController::class, 'trash']);
    Route::post('agencies/{id}/restore', [AgencyController::class, 'restore']);
    Route::delete('agencies/{id}/force', [AgencyController::class, 'forceDestroy']);
    Route::apiResource('agencies', AgencyController::class);
    Route::get('requests/trash', [RequestController::class, 'trash']);
    Route::post('requests/{id}/restore', [RequestController::class, 'restore']);
    Route::delete('requests/{id}/force', [RequestController::class, 'forceDestroy']);
    Route::apiResource('requests', RequestController::class);
    Route::get('planting-monitorings/trash', [PlantingMonitoringController::class, 'trash']);
    Route::post('planting-monitorings/{id}/restore', [PlantingMonitoringController::class, 'restore']);
    Route::delete('planting-monitorings/{id}/force', [PlantingMonitoringController::class, 'forceDestroy']);
    Route::apiResource('planting-monitorings', PlantingMonitoringController::class);
    Route::get('reports/planting-monitoring/pdf', [PlantingMonitoringController::class, 'exportPdf']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::apiResource('trees', TreeController::class);
});
