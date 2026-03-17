<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\ThemeController;
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

    // Theme info (public, no auth needed)
    Route::get('/theme-info/{slug}', [ThemeController::class, 'themeInfo']);

    // Catalogs (public — lists available plugins/themes)
    Route::get('/plugin-catalog', [CatalogController::class, 'pluginCatalog']);
    Route::get('/theme-catalog', [CatalogController::class, 'themeCatalog']);

    // Check updates (requires valid license + registered domain)
    Route::post('/check-updates', [UpdateController::class, 'checkUpdates'])
        ->middleware([ValidateLicense::class, ValidateDomain::class]);

    // Check theme updates (requires valid license + registered domain)
    Route::post('/check-theme-updates', [UpdateController::class, 'checkThemeUpdates'])
        ->middleware([ValidateLicense::class, ValidateDomain::class]);

    // Plugin download (requires valid license + registered domain)
    Route::get('/download/{slug}/{version}', [DownloadController::class, 'download'])
        ->middleware([ValidateLicense::class, ValidateDomain::class])
        ->name('api.download');

    // Theme download (requires valid license + registered domain)
    Route::get('/theme-download/{slug}/{version}', [ThemeController::class, 'download'])
        ->middleware([ValidateLicense::class, ValidateDomain::class])
        ->name('api.theme.download');
});
