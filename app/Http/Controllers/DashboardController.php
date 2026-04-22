<?php

namespace App\Http\Controllers;

use App\Models\AudioLibrary;
use App\Models\BellSchedule;
use App\Models\BellType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and recent activities.
     */
    public function index()
    {
        // Get statistics
        $activeBellType = BellType::where('is_active', true)->first();

        $stats = [
            'total_users' => User::count(),
            'total_audio' => AudioLibrary::count(),
            'total_schedules' => BellSchedule::count(),
            'total_bell_types' => BellType::count(),
            'active_bell_type' => $activeBellType,
            'active_schedules' => $activeBellType ? BellSchedule::where('bell_type_id', $activeBellType->id)->count() : 0,
        ];

        // Get recent schedules (today)
        $todaySchedules = BellSchedule::with(['bellType', 'audioLibrary'])
            ->whereHas('bellType', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('time', 'asc')
            ->limit(5)
            ->get();

        // Get recent audio uploads
        $recentAudio = AudioLibrary::latest()
            ->limit(5)
            ->get();

        // Get all bell types for quick access
        $bellTypes = BellType::withCount('bellSchedules')
            ->orderBy('is_active', 'desc')
            ->get();

        // Get schedule statistics per bell type
        $scheduleStats = BellType::withCount('bellSchedules')
            ->get()
            ->map(function ($bellType) {
                return [
                    'name' => $bellType->name,
                    'count' => $bellType->bell_schedules_count,
                    'is_active' => $bellType->is_active,
                ];
            });

        return view('dashboard', compact(
            'stats',
            'todaySchedules',
            'recentAudio',
            'bellTypes',
            'scheduleStats'
        ));
    }
}
