<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\UpdateLog;
use App\Services\FileService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DownloadController extends Controller
{
    public function __construct(
        protected FileService $fileService,
    ) {}

    /**
     * GET /api/v1/download/{slug}/{version}
     */
    public function download(Request $request, string $slug, string $version): BinaryFileResponse|Response
    {
        $plugin = Plugin::where('slug', $slug)->where('is_active', true)->first();

        if (! $plugin) {
            return response()->json([
                'success' => false,
                'message' => 'Plugin not found.',
            ], 404);
        }

        // Check license exclusion
        $license = $request->input('_domain')?->licenseKey ?? null;
        if ($license && $license->isPluginExcluded($plugin->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access to this plugin is not permitted for your license.',
            ], 403);
        }

        $pluginVersion = $plugin->versions()
            ->where('version', $version)
            ->first();

        if (! $pluginVersion) {
            return response()->json([
                'success' => false,
                'message' => 'Version not found.',
            ], 404);
        }

        $zipPath = $this->fileService->getZipPath($pluginVersion);

        if (! $zipPath) {
            return response()->json([
                'success' => false,
                'message' => 'Download file not available.',
            ], 404);
        }

        // Log the download
        $domain = $request->input('_domain');
        UpdateLog::create([
            'domain_id' => $domain?->id,
            'plugin_id' => $plugin->id,
            'domain_name' => $domain?->domain ?? $request->input('domain'),
            'plugin_slug' => $plugin->file_slug,
            'to_version' => $version,
            'action' => 'download',
            'status' => 'success',
            'ip_address' => $request->ip(),
        ]);

        return response()->download(
            $zipPath,
            $pluginVersion->file_name ?? $plugin->slug . '-' . $version . '.zip',
            ['Content-Type' => 'application/zip']
        );
    }

    /**
     * GET /api/v1/plugin-info/{slug}
     */
    public function pluginInfo(string $slug): Response
    {
        $plugin = Plugin::where('slug', $slug)
            ->where('is_active', true)
            ->with('latestVersion')
            ->first();

        if (! $plugin) {
            return response()->json([
                'success' => false,
                'message' => 'Plugin not found.',
            ], 404);
        }

        $latest = $plugin->latestVersion;

        return response()->json([
            'success' => true,
            'plugin' => [
                'name' => $plugin->name,
                'slug' => $plugin->file_slug,
                'version' => $latest?->version,
                'author' => $plugin->author,
                'author_uri' => $plugin->author_uri,
                'homepage' => $plugin->homepage,
                'description' => $plugin->description,
                'requires_php' => $latest?->requires_php ?? $plugin->requires_php,
                'requires_wp' => $latest?->requires_wp ?? $plugin->requires_wp,
                'tested_wp' => $latest?->tested_wp ?? $plugin->tested_wp,
                'changelog' => $latest?->changelog,
                'last_updated' => $latest?->released_at?->toDateString(),
                'download_url' => $latest ? route('api.download', [
                    'slug' => $plugin->slug,
                    'version' => $latest->version,
                ]) : null,
            ],
        ]);
    }
}
