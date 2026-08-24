<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HardwareCommandQueue;
use App\Models\HardwareConfig;
use App\Models\HardwareLog;
use App\Models\SpeakerZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HardwareApiController extends Controller
{
    /**
     * Get pending commands for bridge to execute
     *
     * Bridge akan poll endpoint ini setiap 5 detik
     */
    public function getPendingCommands(Request $request)
    {
        try {
            // Update last connected time
            $config = HardwareConfig::primary();
            if ($config) {
                $config->updateStatus('online');
            }

            // Get pending commands that haven't expired AND should run now (scheduled_at <= now)
            $now = now();
            $commands = HardwareCommandQueue::pending()
                ->where(function($query) use ($now) {
                    $query->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', $now);
                })
                ->orderBy('scheduled_at')
                ->limit(10) // Max 10 commands per poll
                ->get()
                ->map(function ($command) {
                    return [
                        'id' => $command->id,
                        'command_type' => $command->command_type,
                        'payload' => $command->payload,
                        'scheduled_at' => $command->scheduled_at?->toIso8601String(),
                    ];
                });

            // Mark expired commands
            $this->markExpiredCommands();

            return response()->json([
                'success' => true,
                'count' => $commands->count(),
                'commands' => $commands,
                'server_time' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching pending commands: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching commands',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report command execution result from bridge
     */
    public function reportResult(Request $request)
    {
        $validated = $request->validate([
            'command_id' => 'required|uuid|exists:hardware_command_queue,id',
            'success' => 'required|boolean',
            'message' => 'nullable|string',
            'execution_time_ms' => 'nullable|integer',
            'bridge_version' => 'nullable|string',
            'response_data' => 'nullable|array',
        ]);

        try {
            $command = HardwareCommandQueue::findOrFail($validated['command_id']);

            // Update command status
            if ($validated['success']) {
                $command->markAsCompleted($validated['response_data'] ?? []);
            } else {
                $command->markAsFailed(
                    $validated['message'] ?? 'Unknown error',
                    $validated['response_data'] ?? []
                );
            }

            // Create hardware log
            HardwareLog::create([
                'command_queue_id' => $command->id,
                'speaker_zone_id' => $command->payload['zone_id'] ?? null,
                'action' => $command->command_type,
                'status' => $validated['success'] ? 'success' : 'failed',
                'message' => $validated['message'] ?? null,
                'request_data' => $command->payload,
                'response_data' => $validated['response_data'] ?? [],
                'execution_time_ms' => $validated['execution_time_ms'] ?? null,
                'bridge_version' => $validated['bridge_version'] ?? null,
                'bridge_ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Result recorded successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error reporting command result: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error recording result',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hardware configuration for bridge
     */
    public function getConfig(Request $request)
    {
        try {
            $config = HardwareConfig::primary();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hardware configuration found'
                ], 404);
            }

            $zones = SpeakerZone::enabled()->get()->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'channel' => $zone->modbus_channel,
                    'default_duration' => $zone->default_duration_seconds,
                ];
            });

            return response()->json([
                'success' => true,
                'config' => [
                    'com_port' => $config->com_port,
                    'baud_rate' => $config->baud_rate,
                    'data_bits' => $config->data_bits,
                    'parity' => $config->parity,
                    'stop_bits' => $config->stop_bits,
                    'modbus_address' => $config->modbus_address,
                    'timeout_ms' => $config->timeout_ms,
                ],
                'zones' => $zones,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching config: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Heartbeat endpoint - bridge pings this to show it's alive
     */
    public function heartbeat(Request $request)
    {
        $config = HardwareConfig::primary();

        if ($config) {
            $config->updateStatus('online');
        }

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Mark expired commands
     */
    private function markExpiredCommands(): void
    {
        $expiredCommands = HardwareCommandQueue::expired()->get();

        foreach ($expiredCommands as $command) {
            $command->markAsExpired();

            // Log the expiration
            HardwareLog::create([
                'command_queue_id' => $command->id,
                'action' => $command->command_type,
                'status' => 'expired',
                'message' => 'Command expired before execution',
                'request_data' => $command->payload,
            ]);
        }
    }
}
