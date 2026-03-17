<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ThemeController extends Controller
{
    /**
     * GET /api/v1/theme-info/{slug}
     */
    public function themeInfo(string $slug): Response
    {
        $theme = Theme::where('slug', $slug)
            ->where('is_active', true)
            ->with('latestVersion')
            ->first();

        if (! $theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found.',
            ], 404);
        }

        $latest = $theme->latestVersion;

        return response()->json([
            'success' => true,
            'theme' => [
                'name' => $theme->name,
                'slug' => $theme->slug,
                'version' => $latest?->version,
                'author' => $theme->author,
                'author_uri' => $theme->author_uri,
                'homepage' => $theme->homepage,
                'description' => $theme->description,
                'requires_php' => $latest?->requires_php ?? $theme->requires_php,
                'requires_wp' => $latest?->requires_wp ?? $theme->requires_wp,
                'tested_wp' => $latest?->tested_wp ?? $theme->tested_wp,
                'changelog' => $latest?->changelog,
                'last_updated' => $latest?->released_at?->toDateString(),
                'download_url' => $latest ? route('api.theme.download', [
                    'slug' => $theme->slug,
                    'version' => $latest->version,
                ]) : null,
            ],
        ]);
    }

    /**
     * GET /api/v1/theme-download/{slug}/{version}
     */
    public function download(Request $request, string $slug, string $version): BinaryFileResponse|Response
    {
        $theme = Theme::where('slug', $slug)->where('is_active', true)->first();

        if (! $theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found.',
            ], 404);
        }

        $themeVersion = $theme->versions()
            ->where('version', $version)
            ->first();

        if (! $themeVersion) {
            return response()->json([
                'success' => false,
                'message' => 'Version not found.',
            ], 404);
        }

        if (! $themeVersion->file_path) {
            return response()->json([
                'success' => false,
                'message' => 'Download file not available.',
            ], 404);
        }

        $path = Storage::disk('local')->path($themeVersion->file_path);

        if (! file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Download file not available.',
            ], 404);
        }

        return response()->download(
            $path,
            $themeVersion->file_name ?? $theme->slug . '-' . $version . '.zip',
            ['Content-Type' => 'application/zip']
        );
    }
}
