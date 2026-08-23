<?php

namespace App\Imports;

use App\Models\AudioLibrary;
use App\Models\BellSchedule;
use App\Models\BellType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class BellSchedulesImport implements ToModel, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    protected $errors = [];
    protected $successCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['jenis_bel']) && empty($row['hari']) && empty($row['waktu'])) {
            return null;
        }

        try {
            // Normalize column names (handle variations in spacing/naming)
            $jenisBel = trim($row['jenis_bel'] ?? '');
            $hari = trim(strtolower($row['hari'] ?? ''));
            $waktu = trim($row['waktu'] ?? '');
            $namaAudio = trim($row['nama_audio'] ?? '');

            // Validate required fields
            if (empty($jenisBel)) {
                $this->errors[] = "Row skipped: Kolom 'jenis_bel' kosong";
                return null;
            }
            if (empty($hari)) {
                $this->errors[] = "Row skipped: Kolom 'hari' kosong untuk jenis bel '{$jenisBel}'";
                return null;
            }
            if (empty($waktu)) {
                $this->errors[] = "Row skipped: Kolom 'waktu' kosong untuk jenis bel '{$jenisBel}'";
                return null;
            }
            if (empty($namaAudio)) {
                $this->errors[] = "Row skipped: Kolom 'nama_audio' kosong untuk jenis bel '{$jenisBel}'";
                return null;
            }

            // Find Bell Type by name (case-insensitive, trim whitespace)
            $bellType = BellType::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($jenisBel)])->first();
            if (!$bellType) {
                $this->errors[] = "Jenis bel '{$jenisBel}' tidak ditemukan di sistem. Gunakan salah satu: " . BellType::pluck('name')->implode(', ');
                return null;
            }

            // Find Audio Library by title (case-insensitive, trim whitespace)
            $audioLibrary = AudioLibrary::whereRaw('LOWER(TRIM(title)) = ?', [strtolower($namaAudio)])->first();
            if (!$audioLibrary) {
                $this->errors[] = "Audio '{$namaAudio}' tidak ditemukan di pustaka audio. Pastikan audio sudah ada di sistem.";
                return null;
            }

            // Map Indonesian day names to English (support both formats)
            $dayMap = [
                'senin' => 'monday',
                'selasa' => 'tuesday',
                'rabu' => 'wednesday',
                'kamis' => 'thursday',
                'jumat' => 'friday',
                'sabtu' => 'saturday',
                'minggu' => 'sunday',
                'monday' => 'monday',
                'tuesday' => 'tuesday',
                'wednesday' => 'wednesday',
                'thursday' => 'thursday',
                'friday' => 'friday',
                'saturday' => 'saturday',
                'sunday' => 'sunday',
            ];

            $englishDay = $dayMap[$hari] ?? null;

            if (!$englishDay) {
                $this->errors[] = "Hari '{$hari}' tidak valid. Gunakan: senin, selasa, rabu, kamis, jumat, sabtu, minggu";
                return null;
            }

            // Normalize time format (handle various formats like 7:00, 07:00, 7.00, etc)
            $waktu = str_replace('.', ':', $waktu);

            // Parse time and ensure HH:MM format
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $waktu, $matches)) {
                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $minute = $matches[2];
                $waktu = "{$hour}:{$minute}";
            } else {
                $this->errors[] = "Format waktu '{$waktu}' tidak valid. Gunakan format HH:MM (contoh: 07:00)";
                return null;
            }

            // Validate time format
            if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/', $waktu)) {
                $this->errors[] = "Waktu '{$waktu}' tidak valid. Pastikan jam 00-23 dan menit 00-59";
                return null;
            }

            $this->successCount++;

            return new BellSchedule([
                'bell_type_id' => $bellType->id,
                'day' => $englishDay,
                'time' => $waktu,
                'audio_library_id' => $audioLibrary->id,
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            \Log::error('Import error:', ['row' => $row, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
