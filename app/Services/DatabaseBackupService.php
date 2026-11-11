<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backup & restore database dalam format JSON tanpa bergantung pada
 * pg_dump / mysqldump (sering tidak tersedia di shared hosting).
 *
 * Restore bersifat REPLACE: isi tabel dikosongkan lalu diisi ulang dari
 * backup di dalam satu transaksi, sehingga tidak ada data duplikat dan
 * jika terjadi error semua perubahan dibatalkan (database tidak kosong).
 */
class DatabaseBackupService
{
    public const FORMAT = 'bel-sekolah-backup';

    // Tabel runtime/sistem yang tidak perlu di-backup maupun di-restore
    protected array $excludedTables = [
        'migrations',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ];

    /**
     * Export semua tabel aplikasi ke array siap di-encode JSON
     */
    public function export(): array
    {
        $tables = [];

        foreach ($this->getTables() as $table) {
            $tables[$table] = DB::table($table)->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return [
            'format' => self::FORMAT,
            'version' => 1,
            'created_at' => now()->toIso8601String(),
            'driver' => DB::connection()->getDriverName(),
            'tables' => $tables,
        ];
    }

    /**
     * Restore database dari data backup. Mengembalikan jumlah baris per tabel.
     */
    public function restore(array $backup): array
    {
        if (($backup['format'] ?? null) !== self::FORMAT || !is_array($backup['tables'] ?? null)) {
            throw new \Exception('File backup JSON tidak valid atau bukan backup dari aplikasi ini.');
        }

        $existing = $this->getTables();
        $tables = array_values(array_intersect($this->sortByDependency($existing), array_keys($backup['tables'])));

        if (empty($tables)) {
            throw new \Exception('Backup tidak berisi tabel yang cocok dengan database saat ini.');
        }

        $result = [];

        DB::transaction(function () use ($tables, $backup, &$result) {
            // Hapus dari tabel anak dulu agar foreign key tidak bentrok
            foreach (array_reverse($tables) as $table) {
                DB::table($table)->delete();
            }

            // Isi dari tabel induk dulu
            foreach ($tables as $table) {
                $columns = Schema::getColumnListing($table);
                $rows = $backup['tables'][$table];

                if ($table === 'bell_schedules') {
                    $rows = $this->uniqueSchedules($rows);
                }

                // Hanya kolom yang masih ada di struktur tabel saat ini
                $rows = array_map(fn ($row) => array_intersect_key((array) $row, array_flip($columns)), $rows);

                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table($table)->insert($chunk);
                }

                $result[$table] = count($rows);
            }

            $this->resetSequences($tables);
        });

        return $result;
    }

    /**
     * Daftar tabel aplikasi di database saat ini
     */
    protected function getTables(): array
    {
        $tables = Schema::getTableListing(Schema::getCurrentSchemaListing(), false);

        return array_values(array_diff($tables, $this->excludedTables));
    }

    /**
     * Urutkan tabel: tabel induk (yang direferensikan foreign key) lebih dulu
     */
    protected function sortByDependency(array $tables): array
    {
        $deps = [];
        foreach ($tables as $table) {
            $deps[$table] = collect(Schema::getForeignKeys($table))
                ->pluck('foreign_table')
                ->filter(fn ($t) => $t !== $table && in_array($t, $tables))
                ->unique()
                ->values()
                ->all();
        }

        $sorted = [];
        while (count($sorted) < count($tables)) {
            $progress = false;
            foreach ($tables as $table) {
                if (!in_array($table, $sorted) && empty(array_diff($deps[$table], $sorted))) {
                    $sorted[] = $table;
                    $progress = true;
                }
            }
            // Ketergantungan melingkar: tambahkan sisanya apa adanya
            if (!$progress) {
                return array_values(array_unique(array_merge($sorted, $tables)));
            }
        }

        return $sorted;
    }

    /**
     * Buang jadwal bel ganda (hari + jam sama), simpan yang pertama
     */
    protected function uniqueSchedules(array $rows): array
    {
        $seen = [];

        return array_values(array_filter($rows, function ($row) use (&$seen) {
            $row = (array) $row;
            $key = ($row['day'] ?? '') . '|' . substr((string) ($row['time'] ?? ''), 0, 5);
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
            return true;
        }));
    }

    /**
     * PostgreSQL: sesuaikan sequence auto-increment dengan id terbesar
     */
    protected function resetSequences(array $tables): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'id')) {
                continue;
            }

            $sequence = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS seq', [$table, 'id'])->seq ?? null;

            if ($sequence) {
                DB::statement(
                    "SELECT setval(?, COALESCE((SELECT MAX(id) FROM \"{$table}\"), 1), (SELECT MAX(id) FROM \"{$table}\") IS NOT NULL)",
                    [$sequence]
                );
            }
        }
    }
}
