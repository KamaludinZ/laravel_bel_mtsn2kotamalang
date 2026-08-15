<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HardwareLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'command_queue_id',
        'speaker_zone_id',
        'action',
        'status',
        'message',
        'request_data',
        'response_data',
        'execution_time_ms',
        'bridge_version',
        'bridge_ip',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    /**
     * Get the command queue
     */
    public function commandQueue(): BelongsTo
    {
        return $this->belongsTo(HardwareCommandQueue::class, 'command_queue_id');
    }

    /**
     * Get the speaker zone
     */
    public function speakerZone(): BelongsTo
    {
        return $this->belongsTo(SpeakerZone::class, 'speaker_zone_id');
    }

    /**
     * Scope: Get recent logs
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope: Get logs by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
