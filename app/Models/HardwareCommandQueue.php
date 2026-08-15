<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HardwareCommandQueue extends Model
{
    use HasUuids;

    protected $table = 'hardware_command_queue';

    protected $fillable = [
        'command_type',
        'payload',
        'status',
        'scheduled_at',
        'executed_at',
        'expires_at',
        'error_message',
        'response_data',
        'retry_count',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_data' => 'array',
        'scheduled_at' => 'datetime',
        'executed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get logs for this command
     */
    public function logs(): HasMany
    {
        return $this->hasMany(HardwareLog::class, 'command_queue_id');
    }

    /**
     * Scope: Get pending commands
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Get expired commands
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<=', now());
    }

    /**
     * Mark command as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark command as completed
     */
    public function markAsCompleted(array $responseData = []): void
    {
        $this->update([
            'status' => 'completed',
            'executed_at' => now(),
            'response_data' => $responseData,
        ]);
    }

    /**
     * Mark command as failed
     */
    public function markAsFailed(string $errorMessage, array $responseData = []): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_data' => $responseData,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Mark command as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'error_message' => 'Command expired before execution',
        ]);
    }
}
