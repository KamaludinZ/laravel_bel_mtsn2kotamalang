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
        'parent_hardware_address',
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

    /**
     * Get parent room by hardware address
     */
    public function getParentRoom(): ?Room
    {
        if (!$this->parent_hardware_address) {
            return null;
        }

        return self::where('hardware_address', $this->parent_hardware_address)->first();
    }

    /**
     * Check if this room is a parent (HORN or CTRL ROOM)
     */
    public function isParent(): bool
    {
        return in_array($this->hardware_address, ['10-4', '11-4'])
            && is_null($this->parent_hardware_address);
    }

    /**
     * Check if this room requires parent activation
     */
    public function requiresParent(): bool
    {
        return !is_null($this->parent_hardware_address)
            && !is_null($this->hardware_address);
    }

    /**
     * Get all child rooms for a parent
     */
    public function getChildRooms()
    {
        if (!$this->hardware_address) {
            return collect();
        }

        return self::where('parent_hardware_address', $this->hardware_address)
            ->active()
            ->get();
    }

    /**
     * Scope: Get all parent rooms (HORN and CTRL ROOM)
     */
    public function scopeParents($query)
    {
        return $query->whereIn('hardware_address', ['10-4', '11-4'])
            ->whereNull('parent_hardware_address');
    }

    /**
     * Scope: Get all child rooms (have parent)
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_hardware_address');
    }
}
