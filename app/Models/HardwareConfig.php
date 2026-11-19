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
        // Bridge melakukan polling tiap ~2 detik. Hindari menulis ke database setiap
        // polling: cukup perbarui jika status berubah atau data sudah > 20 detik.
        // (Batas "online" adalah 2 menit, jadi presisi 20 detik lebih dari cukup.)
        if ($this->last_status === $status
            && $this->last_connected_at
            && $this->last_connected_at->diffInSeconds(now(), true) < 20) {
            return;
        }

        $this->update([
            'last_status' => $status,
            'last_connected_at' => now(),
        ]);
    }
}
