<?php

namespace App\Imports;

use App\Models\AudioLibrary;
use App\Models\BellSchedule;
use App\Models\BellType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BellSchedulesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Expected columns: jenis_bel, hari, waktu, nama_audio

        // Find Bell Type by name
        $bellType = BellType::where('name', $row['jenis_bel'])->first();
        if (!$bellType) {
            return null; // Skip if bell type not found
        }

        // Find Audio Library by title
        $audioLibrary = AudioLibrary::where('title', $row['nama_audio'])->first();
        if (!$audioLibrary) {
            return null; // Skip if audio not found
        }

        // Map Indonesian day names to English
        $dayMap = [
            'senin' => 'monday',
            'selasa' => 'tuesday',
            'rabu' => 'wednesday',
            'kamis' => 'thursday',
            'jumat' => 'friday',
            'sabtu' => 'saturday',
            'minggu' => 'sunday',
        ];

        $day = strtolower($row['hari']);
        $englishDay = $dayMap[$day] ?? null;

        if (!$englishDay) {
            return null; // Skip if day not valid
        }

        return new BellSchedule([
            'bell_type_id' => $bellType->id,
            'day' => $englishDay,
            'time' => $row['waktu'],
            'audio_library_id' => $audioLibrary->id,
        ]);
    }
}
