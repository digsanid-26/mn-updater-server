<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\LicenseExclusion;

class LicenseKey extends Model
{
    protected $fillable = [
        'key',
        'client_name',
        'client_email',
        'max_domains',
        'is_active',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_domains' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(LicenseExclusion::class);
    }

    public function isPluginExcluded(int $pluginId): bool
    {
        return $this->exclusions()
            ->where('item_type', 'plugin')
            ->where('item_id', $pluginId)
            ->where('is_excluded', true)
            ->exists();
    }

    public function isThemeExcluded(int $themeId): bool
    {
        return $this->exclusions()
            ->where('item_type', 'theme')
            ->where('item_id', $themeId)
            ->where('is_excluded', true)
            ->exists();
    }

    /**
     * Generate a unique license key
     */
    public static function generateKey(): string
    {
        do {
            $key = strtoupper(
                Str::random(4) . '-' .
                Str::random(4) . '-' .
                Str::random(4) . '-' .
                Str::random(4)
            );
        } while (static::where('key', $key)->exists());

        return $key;
    }

    /**
     * Check if the license is valid (active and not expired)
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if more domains can be added
     */
    public function canAddDomain(): bool
    {
        return $this->domains()->where('is_active', true)->count() < $this->max_domains;
    }
}
