<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\LicenseKey;
use App\Models\UpdateLog;

class LicenseService
{
    /**
     * Validate a license key and register/verify the domain
     */
    public function validateAndRegister(string $key, string $domain, ?string $siteUrl = null, ?string $ip = null): array
    {
        $license = LicenseKey::where('key', $key)->first();

        if (! $license) {
            return ['valid' => false, 'message' => 'Invalid license key.'];
        }

        if (! $license->isValid()) {
            $reason = ! $license->is_active ? 'License is deactivated.' : 'License has expired.';
            return ['valid' => false, 'message' => $reason];
        }

        // Check if domain is already registered for this license
        $existingDomain = $license->domains()
            ->where('domain', $domain)
            ->first();

        if ($existingDomain) {
            // Reactivate if inactive
            if (! $existingDomain->is_active) {
                $existingDomain->update([
                    'is_active' => true,
                    'site_url' => $siteUrl,
                    'last_check_at' => now(),
                ]);
            } else {
                $existingDomain->update(['last_check_at' => now(), 'site_url' => $siteUrl]);
            }

            $this->log($existingDomain, null, 'validate_license', 'success', $ip);

            return [
                'valid' => true,
                'message' => 'License validated successfully.',
                'domain_id' => $existingDomain->id,
            ];
        }

        // Check if license can add more domains
        if (! $license->canAddDomain()) {
            return [
                'valid' => false,
                'message' => "Domain limit reached ({$license->max_domains}). Deactivate an existing domain first.",
            ];
        }

        // Register new domain
        $newDomain = $license->domains()->create([
            'domain' => $domain,
            'site_url' => $siteUrl,
            'is_active' => true,
            'registered_at' => now(),
            'last_check_at' => now(),
        ]);

        $this->log($newDomain, null, 'validate_license', 'success', $ip);

        return [
            'valid' => true,
            'message' => 'License activated and domain registered successfully.',
            'domain_id' => $newDomain->id,
        ];
    }

    /**
     * Deactivate a license for a specific domain
     */
    public function deactivate(string $key, string $domain, ?string $ip = null): array
    {
        $license = LicenseKey::where('key', $key)->first();

        if (! $license) {
            return ['success' => false, 'message' => 'Invalid license key.'];
        }

        $domainRecord = $license->domains()
            ->where('domain', $domain)
            ->where('is_active', true)
            ->first();

        if (! $domainRecord) {
            return ['success' => false, 'message' => 'Domain not found or already deactivated.'];
        }

        $domainRecord->update(['is_active' => false]);

        $this->log($domainRecord, null, 'deactivate_license', 'success', $ip);

        return ['success' => true, 'message' => 'License deactivated for this domain.'];
    }

    /**
     * Find active domain by license key and domain name
     */
    public function findActiveDomain(string $key, string $domain): ?Domain
    {
        $license = LicenseKey::where('key', $key)->where('is_active', true)->first();

        if (! $license || ! $license->isValid()) {
            return null;
        }

        return $license->domains()
            ->where('domain', $domain)
            ->where('is_active', true)
            ->first();
    }

    private function log(?Domain $domain, $pluginId, string $action, string $status, ?string $ip): void
    {
        UpdateLog::create([
            'domain_id' => $domain?->id,
            'plugin_id' => $pluginId,
            'domain_name' => $domain?->domain,
            'action' => $action,
            'status' => $status,
            'ip_address' => $ip,
        ]);
    }
}
