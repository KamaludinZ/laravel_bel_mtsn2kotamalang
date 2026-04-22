<?php

namespace App\Http\Controllers;

use App\Imports\BellSchedulesImport;
use App\Models\AudioLibrary;
use App\Models\BellSchedule;
use App\Models\BellType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BellScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BellSchedule::with(['bellType', 'audioLibrary']);

        if ($request->filled('bell_type_id')) {
            $query->where('bell_type_id', $request->bell_type_id);
        }

        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        $schedules = $query->orderBy('day')->orderBy('time')->paginate(20);
        $bellTypes = BellType::all();

        return view('bell-schedules.index', compact('schedules', 'bellTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bellTypes = BellType::all();
        $audioLibraries = AudioLibrary::all();
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return view('bell-schedules.create', compact('bellTypes', 'audioLibraries', 'days'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bell_type_id' => 'required|exists:bell_types,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time' => 'required|date_format:H:i',
            'audio_library_id' => 'required|exists:audio_libraries,id',
            'keterangan' => 'nullable|string|max:500',
        ]);

        BellSchedule::create($validated);

        return redirect()->route('bell-schedules.index')
            ->with('success', 'Jadwal Bel berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(BellSchedule $bellSchedule)
    {
        $bellSchedule->load(['bellType', 'audioLibrary']);
        return view('bell-schedules.show', compact('bellSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BellSchedule $bellSchedule)
    {
        $bellTypes = BellType::all();
        $audioLibraries = AudioLibrary::all();
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return view('bell-schedules.edit', compact('bellSchedule', 'bellTypes', 'audioLibraries', 'days'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BellSchedule $bellSchedule)
    {
        $validated = $request->validate([
            'bell_type_id' => 'required|exists:bell_types,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time' => 'required|date_format:H:i',
            'audio_library_id' => 'required|exists:audio_libraries,id',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $bellSchedule->update($validated);

        return redirect()->route('bell-schedules.index')
            ->with('success', 'Jadwal Bel berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BellSchedule $bellSchedule)
    {
        $bellSchedule->delete();

        return redirect()->route('bell-schedules.index')
            ->with('success', 'Jadwal Bel berhasil dihapus');
    }

    /**
     * Show the import form.
     */
    public function importForm()
    {
        return view('bell-schedules.import');
    }

    /**
     * Import schedules from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new BellSchedulesImport, $request->file('file'));

            return redirect()->route('bell-schedules.index')
                ->with('success', 'Jadwal berhasil diimport dari Excel');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import Excel: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="template_jadwal_bel.xlsx"',
        ];

        // Create simple template
        $data = [
            ['jenis_bel', 'hari', 'waktu', 'nama_audio'],
            ['Jadwal Normal', 'senin', '07:00', 'Bel Masuk'],
            ['Jadwal Normal', 'senin', '12:00', 'Bel Istirahat'],
        ];

        return response()->streamDownload(function () use ($data) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray($data);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template_jadwal_bel.xlsx', $headers);
    }
}
