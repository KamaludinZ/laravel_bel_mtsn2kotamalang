<?php

namespace App\Exports;

use App\Models\BellSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BellSchedulesImportableExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $bellTypeId;
    protected $day;

    public function __construct($bellTypeId = null, $day = null)
    {
        $this->bellTypeId = $bellTypeId;
        $this->day = $day;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = BellSchedule::with(['bellType', 'audioLibrary'])
            ->orderBy('day')
            ->orderBy('time');

        // Apply filters if provided
        if ($this->bellTypeId) {
            $query->where('bell_type_id', $this->bellTypeId);
        }

        if ($this->day) {
            $query->where('day', $this->day);
        }

        return $query->get();
    }

    /**
     * Define the headings for the Excel sheet (SAME as import template)
     */
    public function headings(): array
    {
        return [
            'jenis_bel',
            'hari',
            'waktu',
            'nama_audio',
        ];
    }

    /**
     * Map each row to importable format (exactly matches import template)
     */
    public function map($schedule): array
    {
        // Map English day names to Indonesian (lowercase for import consistency)
        $dayMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        return [
            $schedule->bellType->name ?? '',
            $dayMap[$schedule->day] ?? $schedule->day,
            $schedule->time,
            $schedule->audioLibrary->title ?? '',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (header)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'],
                    'bold' => true,
                ],
            ],
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // jenis_bel
            'B' => 12,  // hari
            'C' => 10,  // waktu
            'D' => 25,  // nama_audio
        ];
    }
}
