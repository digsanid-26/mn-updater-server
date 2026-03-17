<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeVersion extends Model
{
    protected $fillable = [
        'theme_id',
        'version',
        'changelog',
        'file_path',
        'file_name',
        'file_size',
        'checksum',
        'requires_php',
        'requires_wp',
        'tested_wp',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'released_at' => 'datetime',
        ];
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
