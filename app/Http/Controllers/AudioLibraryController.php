<?php

namespace App\Http\Controllers;

use App\Models\AudioLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AudioLibraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $audioLibraries = AudioLibrary::latest()->paginate(10);
        return view('audio-libraries.index', compact('audioLibraries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('audio-libraries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'audio_file' => 'required|file|mimes:mp3,wav|max:10240', // max 10MB
        ]);

        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('audio', $filename, 'public');

            AudioLibrary::create([
                'title' => $validated['title'],
                'file_path' => $path,
            ]);

            return redirect()->route('audio-libraries.index')
                ->with('success', 'Audio berhasil ditambahkan');
        }

        return back()->with('error', 'File audio tidak ditemukan');
    }

    /**
     * Display the specified resource.
     */
    public function show(AudioLibrary $audioLibrary)
    {
        return view('audio-libraries.show', compact('audioLibrary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AudioLibrary $audioLibrary)
    {
        return view('audio-libraries.edit', compact('audioLibrary'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AudioLibrary $audioLibrary)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'audio_file' => 'nullable|file|mimes:mp3,wav|max:10240',
        ]);

        $data = [
            'title' => $validated['title'],
        ];

        if ($request->hasFile('audio_file')) {
            // Delete old file
            if ($audioLibrary->file_path) {
                Storage::disk('public')->delete($audioLibrary->file_path);
            }

            // Store new file
            $file = $request->file('audio_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('audio', $filename, 'public');
            $data['file_path'] = $path;
        }

        $audioLibrary->update($data);

        return redirect()->route('audio-libraries.index')
            ->with('success', 'Audio berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AudioLibrary $audioLibrary)
    {
        // Delete file from storage
        if ($audioLibrary->file_path) {
            Storage::disk('public')->delete($audioLibrary->file_path);
        }

        $audioLibrary->delete();

        return redirect()->route('audio-libraries.index')
            ->with('success', 'Audio berhasil dihapus');
    }
}
