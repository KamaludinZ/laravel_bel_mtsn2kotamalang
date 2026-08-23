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

            // Find Bell Type by name with smart matching
            // 1. Try exact match (case-insensitive)
            $bellType = BellType::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($jenisBel)])->first();

            // 2. If not found, try partial/fuzzy match
            if (!$bellType) {
                $searchTerm = strtolower(trim($jenisBel));

                // Try contains match
                $bellType = BellType::whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])->first();

                // Or try reverse - if search term contains the DB value
                if (!$bellType) {
                    $allTypes = BellType::all();
                    foreach ($allTypes as $type) {
                        $typeName = strtolower(trim($type->name));
                        if (str_contains($searchTerm, $typeName) || str_contains($typeName, $searchTerm)) {
                            $bellType = $type;
                            break;
                        }
                    }
                }
            }

            // If still not found, show error with available options
            if (!$bellType) {
                $availableTypes = BellType::pluck('name')->toArray();
                $this->errors[] = "Jenis bel '{$jenisBel}' tidak ditemukan. Yang tersedia: " . implode(', ', $availableTypes);
                return null;
            }

            // Find Audio Library by title with smart matching
            // 1. Try exact match (case-insensitive)
            $audioLibrary = AudioLibrary::whereRaw('LOWER(TRIM(title)) = ?', [strtolower($namaAudio)])->first();

            // 2. If not found, try partial/fuzzy match
            if (!$audioLibrary) {
                $searchTerm = strtolower(trim($namaAudio));

                // Try contains match
                $audioLibrary = AudioLibrary::whereRaw('LOWER(title) LIKE ?', ["%{$searchTerm}%"])->first();

                // Or try reverse - if search term contains the DB value
                if (!$audioLibrary) {
                    $allAudio = AudioLibrary::all();
                    foreach ($allAudio as $audio) {
                        $audioTitle = strtolower(trim($audio->title));
                        if (str_contains($searchTerm, $audioTitle) || str_contains($audioTitle, $searchTerm)) {
                            $audioLibrary = $audio;
                            break;
                        }
                    }
                }
            }

            // If still not found, show error with available options
            if (!$audioLibrary) {
                $sampleAudio = AudioLibrary::limit(5)->pluck('title')->toArray();
                $totalAudio = AudioLibrary::count();
                $availableInfo = "Contoh audio yang tersedia: " . implode(', ', $sampleAudio);
                if ($totalAudio > 5) {
                    $availableInfo .= " (dan " . ($totalAudio - 5) . " lainnya)";
                }
                $this->errors[] = "Audio '{$namaAudio}' tidak ditemukan. {$availableInfo}. Silakan upload audio terlebih dahulu di menu Audio Library.";
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
