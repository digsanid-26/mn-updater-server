<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'file_slug',
        'description',
        'author',
        'author_uri',
        'homepage',
        'requires_php',
        'requires_wp',
        'tested_wp',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PluginVersion::class)->orderByDesc('released_at');
    }

    /**
     * Get the latest version by released_at, falling back to created_at
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(PluginVersion::class)
            ->orderByRaw('COALESCE(released_at, created_at) DESC')
            ->limit(1);
    }

    /**
     * Get the latest version using semantic version comparison
     * This is more accurate than date-based sorting
     */
    public function getLatestVersionAttribute(): ?PluginVersion
    {
        $versions = $this->versions()->get();
        
        if ($versions->isEmpty()) {
            return null;
        }

        return $versions->sortBy(function ($version) {
            // Convert version to comparable format
            // e.g., "1.0.1" -> [1, 0, 1]
            $parts = explode('.', $version->version);
            $normalized = 0;
            foreach ($parts as $i => $part) {
                $normalized += intval($part) * pow(1000, 3 - $i);
            }
            return -$normalized; // Negative for descending order
        })->first();
    }

    public function updateLogs(): HasMany
    {
        return $this->hasMany(UpdateLog::class);
    }
}
