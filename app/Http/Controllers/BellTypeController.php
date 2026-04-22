<?php

namespace App\Http\Controllers;

use App\Models\BellType;
use Illuminate\Http\Request;

class BellTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bellTypes = BellType::withCount('bellSchedules')->latest()->get();
        return view('bell-types.index', compact('bellTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        return view('bell-types.create', compact('days'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active_days' => 'required|array|min:1',
            'active_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'is_automatic' => 'boolean',
        ]);

        BellType::create([
            'name' => $validated['name'],
            'active_days' => $validated['active_days'],
            'is_automatic' => $request->boolean('is_automatic'),
            'is_active' => false,
        ]);

        return redirect()->route('bell-types.index')
            ->with('success', 'Jenis Bel berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(BellType $bellType)
    {
        $bellType->load('bellSchedules.audioLibrary');
        return view('bell-types.show', compact('bellType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BellType $bellType)
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];
        return view('bell-types.edit', compact('bellType', 'days'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BellType $bellType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active_days' => 'required|array|min:1',
            'active_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'is_automatic' => 'boolean',
        ]);

        $bellType->update([
            'name' => $validated['name'],
            'active_days' => $validated['active_days'],
            'is_automatic' => $request->boolean('is_automatic'),
        ]);

        return redirect()->route('bell-types.index')
            ->with('success', 'Jenis Bel berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BellType $bellType)
    {
        $bellType->delete();

        return redirect()->route('bell-types.index')
            ->with('success', 'Jenis Bel berhasil dihapus');
    }

    /**
     * Activate a bell type (set as active, deactivate others).
     */
    public function activate(BellType $bellType)
    {
        // Deactivate all other bell types
        BellType::where('id', '!=', $bellType->id)->update(['is_active' => false]);

        // Activate the selected bell type
        $bellType->update(['is_active' => true]);

        return redirect()->route('bell-types.index')
            ->with('success', 'Jenis Bel "' . $bellType->name . '" berhasil diaktifkan');
    }
}
