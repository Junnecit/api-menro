<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AppNotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PlantingMonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCenterController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestItemController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\TreeReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Temporary signed URLs for <img> display (no Bearer token required).
Route::get('media/profile-photos/{user}', [MediaController::class, 'profilePhoto'])
    ->middleware('signed')
    ->name('media.profile-photo');
Route::get('media/tree-photos/{photo}', [MediaController::class, 'treePhoto'])
    ->middleware('signed')
    ->name('media.tree-photo');

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:registration-otp-send');
    Route::get('admins', [AuthController::class, 'admins'])->middleware('throttle:auth-admins');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp'])
        ->middleware('throttle:registration-otp-verify');
    Route::post('resend-registration-otp', [AuthController::class, 'resendRegistrationOtp'])
        ->middleware('throttle:registration-otp-send');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:reset-password');
    Route::get('google/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('google/callback', [GoogleAuthController::class, 'callback']);
    Route::post('google/exchange', [GoogleAuthController::class, 'exchange'])
        ->middleware('throttle:google-exchange');
    Route::post('google/token', [GoogleAuthController::class, 'token'])
        ->middleware('throttle:google-exchange');

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

    Route::get('notifications', [AppNotificationController::class, 'index']);
    Route::get('notifications/unread-count', [AppNotificationController::class, 'unreadCount']);
    Route::post('notifications/read-all', [AppNotificationController::class, 'markAllRead']);
    Route::post('notifications/{notification}/read', [AppNotificationController::class, 'markRead']);
    Route::post('push-token', [AppNotificationController::class, 'registerPushToken']);
    Route::put('push-preference', [AppNotificationController::class, 'updatePushPreference']);

    // User management is available to admins and super-admins. Ownership is
    // enforced by UserPolicy/scopeVisibleTo: a plain admin only ever sees and
    // acts on their own managed pool, while a super-admin bypasses and sees all.
    Route::middleware('role:admin,super-admin')->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('users/trash', [UserController::class, 'trash']);
        Route::post('users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('users/{id}/force', [UserController::class, 'forceDestroy']);
        Route::apiResource('users', UserController::class);
    });

    // Permission matrix and module access controls are strictly restricted to Super Admin.
    Route::middleware('role:super-admin')->group(function () {
        Route::put('roles/permissions/batch', [RoleController::class, 'batchUpdate']);
        Route::post('roles/permissions/reset-all', [RoleController::class, 'resetAll']);
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
        Route::post('roles/{role}/reset-permissions', [RoleController::class, 'resetPermissions']);
    });

    Route::apiResource('test-items', TestItemController::class);

    Route::get('locations/barangays', [RequestController::class, 'barangays']);
    Route::get('agencies/options', [AgencyController::class, 'options']);
    Route::get('agencies/trash', [AgencyController::class, 'trash']);
    Route::post('agencies/{id}/restore', [AgencyController::class, 'restore']);
    Route::delete('agencies/{id}/force', [AgencyController::class, 'forceDestroy']);
    Route::apiResource('agencies', AgencyController::class);
    Route::get('requests/trash', [RequestController::class, 'trash']);
    Route::get('requests/document-template', [RequestController::class, 'documentTemplate']);
    Route::get('requests/template/pdf', [RequestController::class, 'pdfTemplate']);
    Route::post('requests/analyze-document', [RequestController::class, 'analyzeDocument']);
    Route::post('requests/{id}/restore', [RequestController::class, 'restore']);
    Route::delete('requests/{id}/force', [RequestController::class, 'forceDestroy']);
    // Multipart document replace (browsers cannot attach files to PUT reliably).
    Route::post('requests/{request}/document', [RequestController::class, 'update']);
    Route::get('requests/{request}/document/download', [RequestController::class, 'downloadDocument']);
    Route::get('requests/{request}/pdf', [RequestController::class, 'exportPdf']);
    Route::get('reports/requests/pdf', [RequestController::class, 'exportSummaryPdf']);
    Route::apiResource('requests', RequestController::class);
    Route::get('planting-monitorings/seedling-types', [PlantingMonitoringController::class, 'seedlingTypes']);
    Route::get('planting-monitorings/trash', [PlantingMonitoringController::class, 'trash']);
    Route::post('planting-monitorings/{id}/restore', [PlantingMonitoringController::class, 'restore']);
    Route::delete('planting-monitorings/{id}/force', [PlantingMonitoringController::class, 'forceDestroy']);
    Route::apiResource('planting-monitorings', PlantingMonitoringController::class);
    Route::get('reports/planting-monitoring/pdf', [PlantingMonitoringController::class, 'exportPdf']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::apiResource('trees', TreeController::class);

    // Tree Incident & Inspection Reports
    Route::get('tree-reports/stats', [TreeReportController::class, 'stats']);
    Route::get('tree-reports/trash', [TreeReportController::class, 'trash']);
    Route::post('tree-reports/{id}/restore', [TreeReportController::class, 'restore']);
    Route::delete('tree-reports/{id}/force', [TreeReportController::class, 'forceDestroy']);
    Route::get('reports/tree-reports/pdf', [TreeReportController::class, 'exportPdf']);
    Route::apiResource('tree-reports', TreeReportController::class);

    // Report Center file manager
    Route::get('report-center/browse', [ReportCenterController::class, 'browse']);
    Route::post('report-center/sync-agencies', [ReportCenterController::class, 'syncAgencyFolders']);
    Route::post('report-center/folders', [ReportCenterController::class, 'storeFolder']);
    Route::put('report-center/folders/{report_folder}', [ReportCenterController::class, 'updateFolder']);
    Route::delete('report-center/folders/{report_folder}', [ReportCenterController::class, 'destroyFolder']);
    Route::post('report-center/folders/{id}/restore', [ReportCenterController::class, 'restoreFolder']);
    Route::delete('report-center/folders/{id}/force', [ReportCenterController::class, 'forceDestroyFolder']);
    Route::post('report-center/files', [ReportCenterController::class, 'storeFile']);
    Route::post('report-center/files/from-monitoring-pdf', [ReportCenterController::class, 'saveMonitoringPdf']);
    Route::post('report-center/files/from-tree-reports-pdf', [TreeReportController::class, 'saveToReportCenter']);
    Route::put('report-center/files/{report_file}', [ReportCenterController::class, 'updateFile']);
    Route::delete('report-center/files/{report_file}', [ReportCenterController::class, 'destroyFile']);
    Route::post('report-center/files/{id}/restore', [ReportCenterController::class, 'restoreFile']);
    Route::delete('report-center/files/{id}/force', [ReportCenterController::class, 'forceDestroyFile']);
    Route::get('report-center/files/{report_file}/download', [ReportCenterController::class, 'downloadFile']);
});
