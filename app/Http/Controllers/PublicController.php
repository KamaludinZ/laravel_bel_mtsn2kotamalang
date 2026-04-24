<?php

namespace App\Http\Controllers;

use App\Models\BellSchedule;
use App\Models\BellType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Display the public bell display page.
     */
    public function index()
    {
        $bellTypes = BellType::orderBy('is_active', 'desc')->orderBy('name')->get();
        $activeBellType = BellType::where('is_active', true)->first();
        $audioLibraries = \App\Models\AudioLibrary::orderBy('title')->get();

        return view('public.index', compact('bellTypes', 'activeBellType', 'audioLibraries'));
    }

    /**
     * Activate a bell type from public page.
     */
    public function activateBellType(Request $request)
    {
        $request->validate([
            'bell_type_id' => 'required|exists:bell_types,id',
        ]);

        // Deactivate all bell types
        BellType::query()->update(['is_active' => false]);

        // Activate selected bell type
        $bellType = BellType::findOrFail($request->bell_type_id);
        $bellType->update(['is_active' => true]);

        return redirect()->route('public.index')
            ->with('success', 'Jenis bel "' . $bellType->name . '" berhasil diaktifkan');
    }

    /**
     * Get today's bell schedules for the active bell type.
     */
    public function getTodaySchedule()
    {
        // Get active bell type
        $activeBellType = BellType::where('is_active', true)->first();

        if (!$activeBellType) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jenis bel yang aktif',
                'data' => []
            ]);
        }

        // Get current day in English (lowercase)
        $today = strtolower(Carbon::now()->format('l')); // monday, tuesday, etc.

        // Check if today is in the active days
        if (!in_array($today, $activeBellType->active_days ?? [])) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada jadwal untuk hari ini',
                'bell_type' => $activeBellType->name,
                'is_automatic' => $activeBellType->is_automatic,
                'data' => []
            ]);
        }

        // Get schedules for today
        $schedules = BellSchedule::with('audioLibrary')
            ->where('bell_type_id', $activeBellType->id)
            ->where('day', $today)
            ->orderBy('time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'time' => date('H:i', strtotime($schedule->time)),
                    'time_full' => date('H:i:s', strtotime($schedule->time)),
                    'keterangan' => $schedule->keterangan,
                    'audio' => [
                        'id' => $schedule->audioLibrary->id,
                        'title' => $schedule->audioLibrary->title,
                        'file_url' => asset('storage/' . $schedule->audioLibrary->file_path),
                        'duration' => $schedule->audioLibrary->duration,
                        'duration_formatted' => $schedule->audioLibrary->formatted_duration,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diambil',
            'bell_type' => $activeBellType->name,
            'is_automatic' => $activeBellType->is_automatic,
            'current_time' => Carbon::now()->format('H:i:s'),
            'current_date' => Carbon::now()->format('Y-m-d'),
            'day' => $today,
            'data' => $schedules
        ]);
    }

    /**
     * Get current server time.
     */
    public function getCurrentTime()
    {
        return response()->json([
            'success' => true,
            'time' => Carbon::now()->format('H:i:s'),
            'date' => Carbon::now()->format('Y-m-d'),
            'datetime' => Carbon::now()->toIso8601String(),
        ]);
    }
}
