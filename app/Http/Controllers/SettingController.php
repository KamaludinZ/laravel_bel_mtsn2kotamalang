<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\BellSchedule;
use App\Models\AudioLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    /**
     * Display the settings form.
     */
    public function index()
    {
        $appName = Setting::get('app_name', config('app.name'));
        $appLogo = Setting::get('app_logo');

        // Get monitoring data
        $monitoring = $this->getMonitoringData();

        return view('settings.index', compact('appName', 'appLogo', 'monitoring'));
    }

    /**
     * Get comprehensive monitoring data
     */
    private function getMonitoringData()
    {
        return [
            'system' => $this->getSystemInfo(),
            'performance' => $this->getPerformanceMetrics(),
            'security' => $this->getSecurityMetrics(),
            'logs' => $this->getRecentLogs(),
            'database' => $this->getDatabaseMetrics(),
        ];
    }

    /**
     * Get system information
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
            'disk_usage_percent' => round((1 - (disk_free_space('/') / disk_total_space('/'))) * 100, 2),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        $startTime = microtime(true);

        // Test database query performance
        $dbStartTime = microtime(true);
        User::count();
        $dbQueryTime = round((microtime(true) - $dbStartTime) * 1000, 2);

        // Cache statistics
        $cacheDriver = config('cache.default');

        // Storage statistics
        $storagePath = storage_path();
        $publicStorage = storage_path('app/public');

        $storageSize = 0;
        if (File::exists($publicStorage)) {
            $storageSize = $this->getDirectorySize($publicStorage);
        }

        return [
            'uptime' => $this->getServerUptime(),
            'database_query_time' => $dbQueryTime . ' ms',
            'cache_driver' => $cacheDriver,
            'storage_used' => $this->formatBytes($storageSize),
            'average_response_time' => round((microtime(true) - $startTime) * 1000, 2) . ' ms',
            'total_users' => User::count(),
            'total_schedules' => BellSchedule::count(),
            'total_audio' => AudioLibrary::count(),
        ];
    }

    /**
     * Get security metrics
     */
    private function getSecurityMetrics()
    {
        // Check failed login attempts (from logs)
        $failedLogins = $this->countLogEntries('local.ERROR', 'Unauthenticated', 100);

        // Check for suspicious activities
        $suspiciousActivities = $this->countLogEntries('local.WARNING', '', 50);

        // Last login info
        $lastLogins = DB::table('sessions')
            ->orderBy('last_activity', 'desc')
            ->limit(5)
            ->get();

        // Security checklist
        $securityChecks = [
            'https_enabled' => request()->secure(),
            'debug_mode' => config('app.debug'),
            'app_key_set' => !empty(config('app.key')),
            'csrf_protection' => true,
            'session_driver' => config('session.driver'),
        ];

        return [
            'failed_login_attempts' => $failedLogins,
            'suspicious_activities' => $suspiciousActivities,
            'active_sessions' => DB::table('sessions')->count(),
            'last_logins' => $lastLogins,
            'security_checks' => $securityChecks,
        ];
    }

    /**
     * Get recent logs
     */
    private function getRecentLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logFile)) {
            $fileSize = File::size($logFile);
            $content = File::get($logFile);

            // Parse last 50 lines
            $lines = array_slice(explode("\n", $content), -50);

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                // Parse log entry
                if (preg_match('/\[(.*?)\] (.*?): (.*)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1] ?? '',
                        'level' => $matches[2] ?? '',
                        'message' => substr($matches[3] ?? '', 0, 200),
                    ];
                }
            }

            return [
                'file_size' => $this->formatBytes($fileSize),
                'entries' => array_reverse(array_slice($logs, -20)),
                'total_entries' => count($logs),
            ];
        }

        return [
            'file_size' => '0 B',
            'entries' => [],
            'total_entries' => 0,
        ];
    }

    /**
     * Get database metrics
     */
    private function getDatabaseMetrics()
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $database = config("database.connections.{$connection}.database");

        // Get table sizes
        $tables = [];

        try {
            if ($driver === 'mysql') {
                $tables = DB::select("
                    SELECT table_name,
                           ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                           table_rows as rows
                    FROM information_schema.TABLES
                    WHERE table_schema = ?
                    ORDER BY (data_length + index_length) DESC
                    LIMIT 10
                ", [$database]);
            } elseif ($driver === 'pgsql') {
                // PostgreSQL query
                $tables = DB::select("
                    SELECT
                        schemaname || '.' || tablename AS table_name,
                        pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size_mb,
                        (SELECT count(*) FROM information_schema.tables WHERE table_schema = schemaname AND table_name = tablename) as rows
                    FROM pg_tables
                    WHERE schemaname = 'public'
                    ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC
                    LIMIT 10
                ");
            } elseif ($driver === 'sqlite') {
                // For SQLite
                $tableNames = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                foreach ($tableNames as $table) {
                    if ($table->name !== 'sqlite_sequence') {
                        $count = DB::table($table->name)->count();
                        $tables[] = (object)[
                            'table_name' => $table->name,
                            'size_mb' => 'N/A',
                            'rows' => $count
                        ];
                    }
                }
            } else {
                // Generic fallback for other databases
                $tables[] = (object)[
                    'table_name' => 'Information not available',
                    'size_mb' => 'N/A',
                    'rows' => 0
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Error getting database metrics: ' . $e->getMessage());
            $tables[] = (object)[
                'table_name' => 'Error fetching table data',
                'size_mb' => 'N/A',
                'rows' => 0
            ];
        }

        return [
            'driver' => $driver,
            'database' => $database,
            'connection_status' => 'Connected',
            'tables' => $tables,
        ];
    }

    /**
     * Helper: Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Helper: Get directory size
     */
    private function getDirectorySize($path)
    {
        $size = 0;

        if (File::isDirectory($path)) {
            foreach (File::allFiles($path) as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Helper: Count log entries
     */
    private function countLogEntries($level, $keyword, $limit = 100)
    {
        $logFile = storage_path('logs/laravel.log');
        $count = 0;

        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = array_slice(explode("\n", $content), -$limit);

            foreach ($lines as $line) {
                if (stripos($line, $level) !== false) {
                    if (empty($keyword) || stripos($line, $keyword) !== false) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Helper: Get server uptime
     */
    private function getServerUptime()
    {
        if (function_exists('shell_exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $uptime = shell_exec('uptime -p');
            return $uptime ?: 'N/A';
        }
        return 'N/A (Windows)';
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return redirect()->route('settings.index')
                ->with('success', 'Cache berhasil dibersihkan');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear logs
     */
    public function clearLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (File::exists($logFile)) {
                File::put($logFile, '');
            }

            return redirect()->route('settings.index')
                ->with('success', 'Log berhasil dibersihkan');
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('error', 'Gagal membersihkan log: ' . $e->getMessage());
        }
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Update app name
        Setting::set('app_name', $request->app_name);

        // Update logo if provided
        if ($request->hasFile('app_logo')) {
            // Delete old logo
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $file = $request->file('app_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('logos', $filename, 'public');

            Setting::set('app_logo', $path);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil diupdate');
    }
}
