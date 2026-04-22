<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the settings form.
     */
    public function index()
    {
        $appName = Setting::get('app_name', config('app.name'));
        $appLogo = Setting::get('app_logo');

        return view('settings.index', compact('appName', 'appLogo'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Update app name
        Setting::set('app_name', $request->app_name);

        // Update logo if provided
        if ($request->hasFile('app_logo')) {
            // Delete old logo
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $file = $request->file('app_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('logos', $filename, 'public');

            Setting::set('app_logo', $path);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil diupdate');
    }
}
