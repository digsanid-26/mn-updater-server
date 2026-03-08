<?php

use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\UpdateController;
use App\Http\Middleware\ValidateDomain;
use App\Http\Middleware\ValidateLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| MN Updater API v1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // License management (no middleware — validates internally)
    Route::post('/validate-license', [LicenseController::class, 'validate']);
    Route::post('/deactivate-license', [LicenseController::class, 'deactivate']);

    // Plugin info (public, no auth needed)
    Route::get('/plugin-info/{slug}', [DownloadController::class, 'pluginInfo']);

    // Check updates (requires valid license + registered domain)
    Route::post('/check-updates', [UpdateController::class, 'checkUpdates'])
        ->middleware([ValidateLicense::class, ValidateDomain::class]);

    // Download (requires valid license + registered domain)
    Route::get('/download/{slug}/{version}', [DownloadController::class, 'download'])
        ->middleware([ValidateLicense::class, ValidateDomain::class])
        ->name('api.download');
});
