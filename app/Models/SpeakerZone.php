<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpeakerZone extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'modbus_channel',
        'relay_mode',
        'is_enabled',
        'default_duration_seconds',
        'volume_level',
        'sort_order',
        'schedule_override',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'schedule_override' => 'array',
    ];

    /**
     * Get logs for this zone
     */
    public function logs(): HasMany
    {
        return $this->hasMany(HardwareLog::class, 'speaker_zone_id');
    }

    /**
     * Scope: Get enabled zones only
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true)->orderBy('sort_order');
    }

    /**
     * Scope: Get all zones ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
