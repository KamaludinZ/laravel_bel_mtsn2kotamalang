<?php

namespace App\Http\Controllers;

use App\Imports\BellSchedulesImport;
use App\Exports\BellSchedulesExport;
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
        $audioLibraries = AudioLibrary::all();

        return view('bell-schedules.index', compact('schedules', 'bellTypes', 'audioLibraries'));
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
            $import = new BellSchedulesImport;

            try {
                Excel::import($import, $request->file('file'));
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                \Log::error('Excel file read error:', ['message' => $e->getMessage()]);
                return back()->with('error', 'Format file tidak valid atau file corrupt. Pastikan file adalah Excel/CSV yang valid.');
            }

            $successCount = $import->getSuccessCount();
            $errors = $import->getErrors();

            // Build feedback message
            $message = "Import selesai: {$successCount} jadwal berhasil ditambahkan.";

            if (!empty($errors)) {
                $errorCount = count($errors);
                $message .= " {$errorCount} baris dilewati karena error.";

                // Show first 5 errors as examples
                $errorSamples = array_slice($errors, 0, 5);
                $errorDetails = implode("\n", $errorSamples);

                if ($errorCount > 5) {
                    $errorDetails .= "\n... dan " . ($errorCount - 5) . " error lainnya.";
                }

                \Log::warning('Import errors:', $errors);

                if ($successCount > 0) {
                    return redirect()->route('bell-schedules.index')
                        ->with('warning', $message . "\n\nContoh error:\n" . $errorDetails);
                } else {
                    return back()
                        ->with('error', $message . "\n\nDetail error:\n" . $errorDetails);
                }
            }

            return redirect()->route('bell-schedules.index')
                ->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            \Log::error('Import validation failed:', ['errors' => $errorMessages]);
            return back()
                ->with('error', 'Validasi gagal:\n' . implode("\n", array_slice($errorMessages, 0, 10)));

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during import:', ['message' => $e->getMessage(), 'code' => $e->getCode()]);
            return back()->with('error', 'Error database saat import: ' . $e->getMessage());

        } catch (\Throwable $e) {
            \Log::error('Import exception:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal import file: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }

    /**
     * Export schedules to Excel.
     */
    public function export(Request $request)
    {
        $bellTypeId = $request->input('bell_type_id');
        $day = $request->input('day');

        $filename = 'jadwal_bel_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new BellSchedulesExport($bellTypeId, $day), $filename);
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

    /**
     * Bulk update schedules.
     */
    public function bulkUpdate(Request $request)
    {
        try {
            // Log all request data for debugging
            \Log::info('Bulk Update Request:', $request->all());

            $schedules = $request->input('schedules', []);

            if (empty($schedules)) {
                return redirect()->route('bell-schedules.index')
                    ->with('error', 'Tidak ada data yang diupdate. Data yang diterima: ' . json_encode($request->all()));
            }

            $updatedCount = 0;
            $errors = [];

            foreach ($schedules as $scheduleId => $scheduleData) {
                // Validate each schedule data
                $validator = \Validator::make($scheduleData, [
                    'id' => 'required|exists:bell_schedules,id',
                    'bell_type_id' => 'required|exists:bell_types,id',
                    'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                    'time' => 'required|date_format:H:i',
                    'audio_library_id' => 'required|exists:audio_libraries,id',
                    'keterangan' => 'nullable|string|max:500',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Schedule ID {$scheduleId}: " . implode(', ', $validator->errors()->all());
                    \Log::warning("Validation failed for schedule {$scheduleId}", $validator->errors()->all());
                    continue;
                }

                $schedule = BellSchedule::find($scheduleData['id']);
                if ($schedule) {
                    $schedule->update([
                        'bell_type_id' => $scheduleData['bell_type_id'],
                        'day' => $scheduleData['day'],
                        'time' => $scheduleData['time'],
                        'audio_library_id' => $scheduleData['audio_library_id'],
                        'keterangan' => $scheduleData['keterangan'] ?? null,
                    ]);
                    $updatedCount++;
                    \Log::info("Updated schedule {$scheduleData['id']}");
                }
            }

            if (!empty($errors)) {
                \Log::error('Bulk update errors:', $errors);
            }

            if ($updatedCount > 0) {
                return redirect()->route('bell-schedules.index')
                    ->with('success', "Berhasil mengupdate {$updatedCount} jadwal bel");
            } else {
                return redirect()->route('bell-schedules.index')
                    ->with('error', 'Tidak ada jadwal yang berhasil diupdate. Errors: ' . implode('; ', $errors));
            }
        } catch (\Exception $e) {
            \Log::error('Bulk update exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('bell-schedules.index')
                ->with('error', 'Gagal mengupdate jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete schedules.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'schedule_ids' => 'required|array',
            'schedule_ids.*' => 'exists:bell_schedules,id',
        ]);

        $deletedCount = BellSchedule::whereIn('id', $validated['schedule_ids'])->delete();

        return redirect()->route('bell-schedules.index')
            ->with('success', "Berhasil menghapus {$deletedCount} jadwal bel");
    }
}
