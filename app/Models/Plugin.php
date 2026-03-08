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

    public function latestVersion(): HasOne
    {
        return $this->hasOne(PluginVersion::class)->latestOfMany('released_at');
    }

    public function updateLogs(): HasMany
    {
        return $this->hasMany(UpdateLog::class);
    }
}
