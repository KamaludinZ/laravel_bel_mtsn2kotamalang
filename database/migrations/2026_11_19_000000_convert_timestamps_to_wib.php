<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Aplikasi berpindah dari UTC ke WIB (config app.timezone = Asia/Jakarta).
 *
 * Kolom timestamp (tanpa zona waktu) menyimpan "jam dinding". Data lama ditulis
 * dalam jam UTC, jadi digeser +7 jam agar terbaca benar sebagai WIB.
 *
 * Aman untuk instalasi baru maupun dijalankan ulang di database yang sudah WIB:
 * hanya baris dengan created_at lebih tua dari (sekarang - 1 jam) yang digeser.
 * Data era UTC selalu tertinggal >= 7 jam dari jam WIB saat ini, sedangkan data
 * yang baru dibuat dalam WIB (mis. saat instalasi baru) tidak tersentuh.
 */
return new class extends Migration
{
    /** Tabel internal framework yang tidak menyimpan timestamp jam dinding. */
    private array $skipTables = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches'];

    public function up(): void
    {
        $this->shift(+1);

        // Perintah hardware yang masih menunggu dibuat dengan logika jam lama
        // (sebagian dari scheduler tersimpan dalam jam WIB, sebagian UTC). Jangan
        // sampai tereksekusi di jam yang salah setelah deploy: tandai kedaluwarsa.
        if (Schema::hasTable('hardware_command_queue')) {
            DB::table('hardware_command_queue')
                ->whereIn('status', ['pending', 'processing'])
                ->update(['status' => 'expired']);
        }
    }

    public function down(): void
    {
        $this->shift(-1);
    }

    private function shift(int $direction): void
    {
        $minutes = Carbon::now(config('app.timezone'))->utcOffset() * $direction;
        if ($minutes === 0) {
            return; // aplikasi memakai UTC: tidak ada yang perlu dikonversi
        }

        $driver = DB::connection()->getDriverName();
        $grammar = DB::connection()->getQueryGrammar();

        // Batas pemisah data lama vs data baru (dalam jam dinding saat ini)
        $cutoff = $direction > 0
            ? Carbon::now()->subHour()->format('Y-m-d H:i:s')
            : null;

        foreach (Schema::getTables() as $table) {
            $name = $table['name'];
            if (in_array($name, $this->skipTables, true)) {
                continue;
            }

            $columns = collect(Schema::getColumns($name))
                ->filter(function ($col) {
                    $type = strtolower($col['type_name'] ?? $col['type'] ?? '');
                    $full = strtolower($col['type'] ?? '');
                    $isTimestamp = str_contains($type, 'timestamp') || str_contains($type, 'datetime');
                    $hasZone = str_contains($type, 'tz') || str_contains($full, 'with time zone');
                    return $isTimestamp && !$hasZone;
                })
                ->pluck('name')
                ->all();

            if (empty($columns)) {
                continue;
            }

            $sets = [];
            foreach ($columns as $column) {
                $wrapped = $grammar->wrap($column);
                $sets[] = $wrapped . ' = ' . $this->addMinutes($driver, $wrapped, $minutes);
            }

            $sql = 'UPDATE ' . $grammar->wrapTable($name) . ' SET ' . implode(', ', $sets);
            $bindings = [];

            if ($cutoff !== null) {
                $anchor = in_array('created_at', $columns, true) ? 'created_at' : $columns[0];
                $sql .= ' WHERE ' . $grammar->wrap($anchor) . ' IS NULL OR ' . $grammar->wrap($anchor) . ' < ?';
                $bindings[] = $cutoff;
            }

            $affected = DB::update($sql, $bindings);
            Log::info("Konversi timestamp ke WIB: {$name} (" . implode(', ', $columns) . ") -> {$affected} baris");
        }
    }

    /**
     * Ekspresi SQL "kolom + N menit" per driver. Kolom NULL tetap NULL.
     */
    private function addMinutes(string $driver, string $column, int $minutes): string
    {
        $sign = $minutes >= 0 ? '+' : '-';
        $abs = abs($minutes);

        return match ($driver) {
            'pgsql' => "{$column} {$sign} interval '{$abs} minutes'",
            'mysql', 'mariadb' => "DATE_ADD({$column}, INTERVAL {$minutes} MINUTE)",
            'sqlsrv' => "DATEADD(minute, {$minutes}, {$column})",
            default => "datetime({$column}, '{$sign}{$abs} minutes')", // sqlite
        };
    }
};
