<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseExclusion extends Model
{
    protected $fillable = [
        'license_key_id',
        'item_type',
        'item_id',
        'is_excluded',
    ];

    protected function casts(): array
    {
        return [
            'is_excluded' => 'boolean',
        ];
    }

    public function licenseKey(): BelongsTo
    {
        return $this->belongsTo(LicenseKey::class);
    }

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class, 'item_id');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'item_id');
    }
}
