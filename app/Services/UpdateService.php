<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Plugin;
use App\Models\UpdateLog;
use App\Models\LicenseKey;

class UpdateService
{
    /**
     * Check for available updates given installed plugins and their versions
     *
     * @param array $installedPlugins slug => version pairs
     * @param Domain $domain The requesting domain
     * @param string|null $ip Request IP
     * @return array List of available updates
     */
    public function checkUpdates(array $installedPlugins, Domain $domain, ?string $ip = null): array
    {
        $updates = [];

        // Update last_check_at
        $domain->update(['last_check_at' => now()]);

        // Load excluded plugin IDs for this license
        $license = $domain->licenseKey;
        $excludedPluginIds = $license
            ? $license->exclusions()->where('item_type', 'plugin')->where('is_excluded', true)->pluck('item_id')->toArray()
            : [];

        $activePlugins = Plugin::where('is_active', true)
            ->with('versions')
            ->get()
            ->keyBy('file_slug');

        foreach ($installedPlugins as $slug => $currentVersion) {
            if (! isset($activePlugins[$slug])) {
                continue;
            }

            $plugin = $activePlugins[$slug];

            // Skip if excluded for this license
            if (in_array($plugin->id, $excludedPluginIds)) {
                continue;
            }
            // Use semantic version comparison via attribute
            $latest = $plugin->latest_version;

            if (! $latest) {
                continue;
            }

            if (version_compare($currentVersion, $latest->version, '<')) {
                $updates[] = [
                    'slug' => $plugin->file_slug,
                    'name' => $plugin->name,
                    'version' => $latest->version,
                    'download_url' => route('api.download', [
                        'slug' => $plugin->slug,
                        'version' => $latest->version,
                    ]),
                    'homepage' => $plugin->homepage ?? 'https://www.digsan.id/',
                    'description' => $plugin->description,
                    'requires_php' => $latest->requires_php ?? $plugin->requires_php,
                    'requires_wp' => $latest->requires_wp ?? $plugin->requires_wp,
                    'tested_wp' => $latest->tested_wp ?? $plugin->tested_wp,
                    'changelog' => $latest->changelog,
                    'last_updated' => $latest->released_at?->toDateString(),
                ];

                // Log the check
                UpdateLog::create([
                    'domain_id' => $domain->id,
                    'plugin_id' => $plugin->id,
                    'domain_name' => $domain->domain,
                    'plugin_slug' => $plugin->file_slug,
                    'from_version' => $currentVersion,
                    'to_version' => $latest->version,
                    'action' => 'check_update',
                    'status' => 'success',
                    'ip_address' => $ip,
                ]);
            }
        }

        return $updates;
    }
}
