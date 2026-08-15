<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'no',
        'room_code',
        'room_type',
        'room_name',
        'group_name',
        'hardware_address',
        'speaker_zone_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Room belongs to SpeakerZone
     */
    public function speakerZone(): BelongsTo
    {
        return $this->belongsTo(SpeakerZone::class);
    }

    /**
     * Scope: Get only active rooms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('no');
    }

    /**
     * Scope: Filter by group
     */
    public function scopeByGroup($query, string $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    /**
     * Scope: Filter by room type
     */
    public function scopeByType($query, string $roomType)
    {
        return $query->where('room_type', $roomType);
    }

    /**
     * Get all unique groups
     */
    public static function getAllGroups(): array
    {
        return self::select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();
    }

    /**
     * Get all unique room types
     */
    public static function getAllTypes(): array
    {
        return self::select('room_type')
            ->distinct()
            ->orderBy('room_type')
            ->pluck('room_type')
            ->toArray();
    }
}
