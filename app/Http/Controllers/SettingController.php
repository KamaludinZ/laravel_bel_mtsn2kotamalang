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
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class SettingController extends Controller
{
    /**
     * Display the settings form.
     */
    public function index()
    {
        $appName = Setting::get('app_name', config('app.name'));
        $appLogo = Setting::get('app_logo');
        $hardwareEnabled = Setting::get('hardware_integration_enabled', 'false') === 'true';

        // Get monitoring data
        $monitoring = $this->getMonitoringData();

        // Get backups list
        $backups = $this->listBackups();

        return view('settings.index', compact('appName', 'appLogo', 'monitoring', 'hardwareEnabled', 'backups'));
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

    private function getRepositoryPath(): ?string
    {
        $configuredPath = Setting::get('update_repo_path');
        $candidates = array_filter([
            $configuredPath ?: null,
            base_path(),
            base_path('..'),
            public_path('..'),
        ]);

        foreach ($candidates as $path) {
            $realPath = realpath($path);
            if (!$realPath || !is_dir($realPath)) {
                Log::debug("getRepositoryPath: Path tidak valid: {$path}");
                continue;
            }

            // Check if .git directory exists
            if (!is_dir($realPath . '/.git')) {
                Log::debug("getRepositoryPath: .git tidak ditemukan di: {$realPath}");
                continue;
            }

            try {
                $process = new Process(['git', 'rev-parse', '--is-inside-work-tree'], $realPath);
                $process->run();

                if ($process->isSuccessful() && trim($process->getOutput()) === 'true') {
                    Log::info("getRepositoryPath: Git repo ditemukan di: {$realPath}");
                    return $realPath;
                }
            } catch (\Exception $e) {
                Log::debug("getRepositoryPath: Error git command di {$realPath}: " . $e->getMessage());
                continue;
            }
        }

        Log::error("getRepositoryPath: Tidak menemukan git repository di semua kandidat path");
        return null;
    }

    /**
     * Check update availability from GitHub based on app version.
     */
    public function checkGithubUpdate()
    {
        try {
            $currentVersion = (string) config('app.version', 'v1.0.1');

            $response = Http::timeout(20)
                ->acceptJson()
                ->get('https://api.github.com/repos/KamaludinZ/laravel_bel_mtsn2kotamalang/releases/latest');

            if (!$response->successful()) {
                return redirect()->route('settings.index')
                    ->with('error', 'Gagal mengambil versi terbaru dari GitHub.')
                    ->with('update_info', [
                        'current_version' => $currentVersion,
                    ]);
            }

            $latestTag = (string) data_get($response->json(), 'tag_name', '');
            if ($latestTag === '') {
                return redirect()->route('settings.index')
                    ->with('error', 'Tag versi terbaru di GitHub tidak ditemukan.')
                    ->with('update_info', [
                        'current_version' => $currentVersion,
                    ]);
            }

            $currentNormalized = ltrim(strtolower($currentVersion), 'v');
            $latestNormalized = ltrim(strtolower($latestTag), 'v');

            $needsUpdate = version_compare($latestNormalized, $currentNormalized, '>');

            return redirect()->route('settings.index')
                ->with('update_info', [
                    'current_version' => $currentVersion,
                    'latest_version' => $latestTag,
                    'needs_update' => $needsUpdate,
                ])
                ->with(
                    $needsUpdate ? 'success' : 'success',
                    $needsUpdate
                        ? 'Update tersedia. Versi terbaru ditemukan di GitHub.'
                        : 'Aplikasi sudah menggunakan versi terbaru.'
                );
        } catch (\Throwable $e) {
            Log::error('Check update error: ' . $e->getMessage());

            return redirect()->route('settings.index')
                ->with('error', 'Terjadi kesalahan saat memeriksa update: ' . $e->getMessage())
                ->with('update_info', [
                    'current_version' => (string) config('app.version', 'v1.0.1'),
                ]);
        }
    }

    /**
     * Run update process from GitHub after user confirmation.
     */
    public function runGithubUpdate(Request $request)
    {
        $request->validate([
            'confirm_update' => 'required|accepted',
        ]);

        try {
            $repoPath = $this->getRepositoryPath();
            if (!$repoPath) {
                return redirect()->route('settings.index')
                    ->with('error', 'Repository git tidak ditemukan di direktori aplikasi.');
            }

            $outputLogs = [];
            $originProcess = new Process(['git', 'remote', 'get-url', 'origin'], $repoPath);
            $originProcess->run();

            if (!$originProcess->isSuccessful()) {
                return redirect()->route('settings.index')
                    ->with('error', 'Gagal membaca remote origin repository.');
            }

            $originUrl = trim($originProcess->getOutput());
            if (stripos($originUrl, 'KamaludinZ/laravel_bel_mtsn2kotamalang') === false) {
                return redirect()->route('settings.index')
                    ->with('error', 'Remote origin tidak sesuai dengan repository update yang ditentukan.');
            }

            $branchProcess = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], $repoPath);
            $branchProcess->run();
            $branch = $branchProcess->isSuccessful() ? trim($branchProcess->getOutput()) : 'main';

            $commands = [
                ['git', 'fetch', 'origin'],
                ['git', 'reset', '--hard', "origin/{$branch}"],
                ['composer', 'install', '--no-interaction', '--prefer-dist', '--optimize-autoloader'],
                ['php', 'artisan', 'migrate', '--force'],
                ['php', 'artisan', 'optimize:clear'],
            ];

            foreach ($commands as $command) {
                $process = new Process($command, $repoPath);
                $process->setTimeout(600);
                $process->run();

                $outputLogs[] = '$ ' . implode(' ', $command);
                $combinedOutput = trim($process->getOutput() . PHP_EOL . $process->getErrorOutput());
                if (!empty($combinedOutput)) {
                    $outputLogs[] = $combinedOutput;
                }

                if (!$process->isSuccessful()) {
                    return redirect()->route('settings.index')
                        ->with('error', 'Proses update gagal pada perintah: ' . implode(' ', $command))
                        ->with('update_logs', $outputLogs);
                }
            }

            return redirect()->route('settings.index')
                ->with('success', 'Update berhasil diproses ke versi terbaru (v1.0.1).')
                ->with('update_logs', $outputLogs);
        } catch (\Throwable $e) {
            Log::error('Run update error: ' . $e->getMessage());

            return redirect()->route('settings.index')
                ->with('error', 'Terjadi kesalahan saat proses update: ' . $e->getMessage());
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

    /**
     * Toggle hardware integration
     */
    public function toggleHardware(Request $request)
    {
        $enabled = $request->has('hardware_enabled') && $request->hardware_enabled == '1';

        Setting::set('hardware_integration_enabled', $enabled ? 'true' : 'false');

        $message = $enabled
            ? 'Hardware integration diaktifkan. Speaker fisik akan ikut bunyi saat bel otomatis.'
            : 'Hardware integration dinonaktifkan. Audio hanya akan diputar di browser.';

        Log::info('Hardware integration ' . ($enabled ? 'enabled' : 'disabled') . ' by user: ' . auth()->user()->name);

        return redirect()->route('settings.index')
            ->with('success', $message);
    }

    /**
     * List all available backups
     */
    public function listBackups()
    {
        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $backups = collect(File::files($backupPath))
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            })
            ->sortByDesc('timestamp')
            ->values()
            ->all();

        return $backups;
    }

    /**
     * Create full backup (database + files)
     */
    public function createBackup(Request $request)
    {
        $request->validate([
            'backup_type' => 'required|in:full,database,files',
        ]);

        try {
            $timestamp = date('Y-m-d_His');
            $backupType = $request->backup_type;
            $backupPath = storage_path('app/backups');

            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $logs = [];

            // Backup Database
            if (in_array($backupType, ['full', 'database'])) {
                $dbBackup = $this->backupDatabase($timestamp);
                $logs[] = $dbBackup['message'];
            }

            // Backup Files
            if (in_array($backupType, ['full', 'files'])) {
                $filesBackup = $this->backupFiles($timestamp);
                $logs[] = $filesBackup['message'];
            }

            return redirect()->route('settings.index')
                ->with('success', 'Backup berhasil dibuat!')
                ->with('backup_logs', $logs);

        } catch (\Exception $e) {
            Log::error('Backup error: ' . $e->getMessage());
            return redirect()->route('settings.index')
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Backup database to SQL file
     */
    private function backupDatabase($timestamp)
    {
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        $backupPath = storage_path('app/backups');
        $filename = "database_{$timestamp}.sql";
        $filepath = "{$backupPath}/{$filename}";

        if ($dbConfig['driver'] === 'pgsql') {
            // PostgreSQL backup
            $command = sprintf(
                'pg_dump -h %s -p %s -U %s -d %s > %s',
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            // Set PGPASSWORD environment variable
            putenv("PGPASSWORD=" . $dbConfig['password']);

            exec($command . ' 2>&1', $output, $returnCode);

            putenv("PGPASSWORD");

            if ($returnCode !== 0) {
                throw new \Exception("PostgreSQL backup failed: " . implode("\n", $output));
            }

        } elseif ($dbConfig['driver'] === 'mysql') {
            // MySQL backup
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s > %s',
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port'] ?? 3306),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("MySQL backup failed: " . implode("\n", $output));
            }
        } else {
            throw new \Exception("Database driver {$dbConfig['driver']} not supported for backup");
        }

        $size = $this->formatBytes(filesize($filepath));
        return [
            'success' => true,
            'file' => $filename,
            'message' => "Database backup created: {$filename} ({$size})"
        ];
    }

    /**
     * Backup important files (storage, .env, etc)
     */
    private function backupFiles($timestamp)
    {
        $backupPath = storage_path('app/backups');
        $filename = "files_{$timestamp}.zip";
        $filepath = "{$backupPath}/{$filename}";

        $zip = new \ZipArchive();

        if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create ZIP file");
        }

        // Backup directories
        $directoriesToBackup = [
            storage_path('app/public/audio'),
            storage_path('app/public/logos'),
        ];

        // Backup files
        $filesToBackup = [
            base_path('.env'),
        ];

        // Add directories
        foreach ($directoriesToBackup as $dir) {
            if (File::exists($dir)) {
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }
        }

        // Add individual files
        foreach ($filesToBackup as $file) {
            if (File::exists($file)) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                $zip->addFile($file, $relativePath);
            }
        }

        $zip->close();

        $size = $this->formatBytes(filesize($filepath));
        return [
            'success' => true,
            'file' => $filename,
            'message' => "Files backup created: {$filename} ({$size})"
        ];
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return redirect()->route('settings.index')
                ->with('error', 'File backup tidak ditemukan');
        }

        return response()->download($filepath);
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            return redirect()->route('settings.index')
                ->with('error', 'File backup tidak ditemukan');
        }

        File::delete($filepath);

        return redirect()->route('settings.index')
            ->with('success', 'Backup berhasil dihapus: ' . $filename);
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
            'confirm_restore' => 'required|accepted',
        ]);

        try {
            $filename = $request->backup_file;
            $filepath = storage_path('app/backups/' . $filename);

            if (!File::exists($filepath)) {
                throw new \Exception('File backup tidak ditemukan');
            }

            $logs = [];

            // Restore database
            if (str_contains($filename, 'database_') && str_ends_with($filename, '.sql')) {
                $restoreDb = $this->restoreDatabase($filepath);
                $logs[] = $restoreDb['message'];
            }

            // Restore files
            if (str_contains($filename, 'files_') && str_ends_with($filename, '.zip')) {
                $restoreFiles = $this->restoreFiles($filepath);
                $logs[] = $restoreFiles['message'];
            }

            // Clear caches after restore
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            $logs[] = 'Cache cleared successfully';

            return redirect()->route('settings.index')
                ->with('success', 'Restore berhasil!')
                ->with('backup_logs', $logs);

        } catch (\Exception $e) {
            Log::error('Restore error: ' . $e->getMessage());
            return redirect()->route('settings.index')
                ->with('error', 'Gagal restore backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from SQL file
     */
    private function restoreDatabase($filepath)
    {
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        if ($dbConfig['driver'] === 'pgsql') {
            // PostgreSQL restore
            $command = sprintf(
                'psql -h %s -p %s -U %s -d %s < %s',
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port']),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            putenv("PGPASSWORD=" . $dbConfig['password']);
            exec($command . ' 2>&1', $output, $returnCode);
            putenv("PGPASSWORD");

            if ($returnCode !== 0) {
                throw new \Exception("PostgreSQL restore failed: " . implode("\n", $output));
            }

        } elseif ($dbConfig['driver'] === 'mysql') {
            // MySQL restore
            $command = sprintf(
                'mysql -h %s -P %s -u %s -p%s %s < %s',
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['port'] ?? 3306),
                escapeshellarg($dbConfig['username']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );

            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("MySQL restore failed: " . implode("\n", $output));
            }
        }

        return [
            'success' => true,
            'message' => 'Database restored successfully from: ' . basename($filepath)
        ];
    }

    /**
     * Restore files from ZIP
     */
    private function restoreFiles($filepath)
    {
        $zip = new \ZipArchive();

        if ($zip->open($filepath) !== true) {
            throw new \Exception("Cannot open ZIP file");
        }

        $zip->extractTo(base_path());
        $zip->close();

        return [
            'success' => true,
            'message' => 'Files restored successfully from: ' . basename($filepath)
        ];
    }

}
