<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ThemeUpdateService;
use App\Services\UpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(
        protected UpdateService $updateService,
        protected ThemeUpdateService $themeUpdateService,
    ) {}

    /**
     * POST /api/v1/check-updates
     */
    public function checkUpdates(Request $request): JsonResponse
    {
        $request->validate([
            'plugins' => 'required|array',
            'plugins.*' => 'required|string',
        ]);

        $domain = $request->input('_domain');
        $plugins = $request->input('plugins');
        $ip = $request->ip();

        $updates = $this->updateService->checkUpdates($plugins, $domain, $ip);

        return response()->json([
            'success' => true,
            'updates' => $updates,
        ]);
    }

    /**
     * POST /api/v1/check-theme-updates
     */
    public function checkThemeUpdates(Request $request): JsonResponse
    {
        $request->validate([
            'themes' => 'required|array',
            'themes.*' => 'required|string',
        ]);

        $domain = $request->input('_domain');
        $themes = $request->input('themes');
        $ip = $request->ip();

        $updates = $this->themeUpdateService->checkUpdates($themes, $domain, $ip);

        return response()->json([
            'success' => true,
            'updates' => $updates,
        ]);
    }
}
