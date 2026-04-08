<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Theme;
use App\Models\LicenseKey;

class ThemeUpdateService
{
    /**
     * Check for available theme updates given installed themes and their versions
     *
     * @param array $installedThemes slug => version pairs
     * @param Domain $domain The requesting domain
     * @param string|null $ip Request IP
     * @return array List of available updates
     */
    public function checkUpdates(array $installedThemes, Domain $domain, ?string $ip = null): array
    {
        $updates = [];

        // Load excluded theme IDs for this license
        $license = $domain->licenseKey;
        $excludedThemeIds = $license
            ? $license->exclusions()->where('item_type', 'theme')->where('is_excluded', true)->pluck('item_id')->toArray()
            : [];

        $activeThemes = Theme::where('is_active', true)
            ->with('versions')
            ->get()
            ->keyBy('slug');

        foreach ($installedThemes as $slug => $currentVersion) {
            if (! isset($activeThemes[$slug])) {
                continue;
            }

            $theme = $activeThemes[$slug];

            // Skip if excluded for this license
            if (in_array($theme->id, $excludedThemeIds)) {
                continue;
            }
            $latest = $theme->latest_version;

            if (! $latest) {
                continue;
            }

            if (version_compare($currentVersion, $latest->version, '<')) {
                $updates[] = [
                    'slug' => $theme->slug,
                    'name' => $theme->name,
                    'version' => $latest->version,
                    'download_url' => route('api.theme.download', [
                        'slug' => $theme->slug,
                        'version' => $latest->version,
                    ]),
                    'homepage' => $theme->homepage ?? 'https://www.digsan.id/',
                    'description' => $theme->description,
                    'requires_php' => $latest->requires_php ?? $theme->requires_php,
                    'requires_wp' => $latest->requires_wp ?? $theme->requires_wp,
                    'tested_wp' => $latest->tested_wp ?? $theme->tested_wp,
                    'changelog' => $latest->changelog,
                    'last_updated' => $latest->released_at?->toDateString(),
                ];
            }
        }

        return $updates;
    }
}
