<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HardwareConfig extends Model
{
    use HasUuids;

    protected $fillable = [
        'config_key',
        'device_type',
        'connection_type',
        'com_port',
        'baud_rate',
        'data_bits',
        'parity',
        'stop_bits',
        'modbus_address',
        'is_enabled',
        'auto_reconnect',
        'timeout_ms',
        'extra_config',
        'last_connected_at',
        'last_status',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_reconnect' => 'boolean',
        'extra_config' => 'array',
        'last_connected_at' => 'datetime',
    ];

    /**
     * Get primary device config
     */
    public static function primary()
    {
        return self::where('config_key', 'primary_device')->first();
    }

    /**
     * Check if device is online
     */
    public function isOnline(): bool
    {
        if (!$this->last_connected_at) {
            return false;
        }

        // Consider offline if no connection in last 2 minutes
        return $this->last_connected_at->diffInMinutes(now()) < 2;
    }

    /**
     * Update connection status
     */
    public function updateStatus(string $status): void
    {
        $this->update([
            'last_status' => $status,
            'last_connected_at' => now(),
        ]);
    }
}
