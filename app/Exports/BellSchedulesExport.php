<?php

namespace App\Exports;

use App\Models\BellSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BellSchedulesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
     * Define the headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            'No',
            'Jenis Bel',
            'Hari',
            'Waktu',
            'Nama Audio',
            'Keterangan',
            'Dibuat Pada',
        ];
    }

    /**
     * Map each row to the desired format
     */
    public function map($schedule): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Map English day names to Indonesian
        $dayMap = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return [
            $rowNumber,
            $schedule->bellType->name ?? '-',
            $dayMap[$schedule->day] ?? $schedule->day,
            $schedule->time,
            $schedule->audioLibrary->title ?? '-',
            $schedule->keterangan ?? '-',
            $schedule->created_at->format('d/m/Y H:i'),
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
            'A' => 6,   // No
            'B' => 20,  // Jenis Bel
            'C' => 12,  // Hari
            'D' => 10,  // Waktu
            'E' => 25,  // Nama Audio
            'F' => 30,  // Keterangan
            'G' => 18,  // Dibuat Pada
        ];
    }
}
