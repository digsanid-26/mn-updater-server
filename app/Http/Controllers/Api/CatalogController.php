<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LicenseKey;
use App\Models\Plugin;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * GET /api/v1/plugin-catalog
     * Returns all active plugins with their latest version info.
     */
    public function pluginCatalog(Request $request): JsonResponse
    {
        $license = null;
        $licenseKey = $request->input('license_key');
        if ($licenseKey) {
            $license = LicenseKey::where('key', $licenseKey)->where('is_active', true)->first();
        }

        $excludedIds = $license
            ? $license->exclusions()->where('item_type', 'plugin')->where('is_excluded', true)->pluck('item_id')->toArray()
            : [];

        $plugins = Plugin::where('is_active', true)
            ->with('latestVersion')
            ->get();

        $catalog = $plugins->map(function ($plugin) use ($excludedIds) {
            $latest = $plugin->latestVersion;
            return [
                'name'        => $plugin->name,
                'slug'        => $plugin->slug,
                'file_slug'   => $plugin->file_slug,
                'description' => $plugin->description,
                'author'      => $plugin->author,
                'author_uri'  => $plugin->author_uri,
                'homepage'    => $plugin->homepage ?? 'https://www.digsan.id/',
                'version'     => $latest?->version,
                'requires_php' => $latest?->requires_php ?? $plugin->requires_php,
                'requires_wp'  => $latest?->requires_wp ?? $plugin->requires_wp,
                'tested_wp'    => $latest?->tested_wp ?? $plugin->tested_wp,
                'last_updated' => $latest?->released_at?->toDateString(),
                'excluded'     => in_array($plugin->id, $excludedIds),
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'plugins' => $catalog,
        ]);
    }

    /**
     * GET /api/v1/theme-catalog
     * Returns all active themes with their latest version info.
     */
    public function themeCatalog(Request $request): JsonResponse
    {
        $license = null;
        $licenseKey = $request->input('license_key');
        if ($licenseKey) {
            $license = LicenseKey::where('key', $licenseKey)->where('is_active', true)->first();
        }

        $excludedIds = $license
            ? $license->exclusions()->where('item_type', 'theme')->where('is_excluded', true)->pluck('item_id')->toArray()
            : [];

        $themes = Theme::where('is_active', true)
            ->with('latestVersion')
            ->get();

        $catalog = $themes->map(function ($theme) use ($excludedIds) {
            $latest = $theme->latestVersion;
            return [
                'name'        => $theme->name,
                'slug'        => $theme->slug,
                'description' => $theme->description,
                'author'      => $theme->author,
                'author_uri'  => $theme->author_uri,
                'homepage'    => $theme->homepage ?? 'https://www.digsan.id/',
                'version'     => $latest?->version,
                'requires_php' => $latest?->requires_php ?? $theme->requires_php,
                'requires_wp'  => $latest?->requires_wp ?? $theme->requires_wp,
                'tested_wp'    => $latest?->tested_wp ?? $theme->tested_wp,
                'last_updated' => $latest?->released_at?->toDateString(),
                'excluded'     => in_array($theme->id, $excludedIds),
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'themes'  => $catalog,
        ]);
    }
}
