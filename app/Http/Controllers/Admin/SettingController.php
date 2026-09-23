<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $office = OfficeSetting::getSetting();
        return view('admin.settings.index', compact('office'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'office_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
        ], [
            'office_name.required' => 'Nama instansi / kantor wajib diisi.',
            'latitude.required' => 'Titik Latitude wajib diisi.',
            'longitude.required' => 'Titik Longitude wajib diisi.',
            'radius_meters.required' => 'Radius toleransi (meter) wajib diisi.',
            'work_start_time.required' => 'Batas waktu masuk wajib diisi.',
            'work_end_time.required' => 'Waktu pulang wajib diisi.',
        ]);

        // Tambahkan detik :00 jika format H:i
        if (strlen($validated['work_start_time']) === 5) {
            $validated['work_start_time'] .= ':00';
        }
        if (strlen($validated['work_end_time']) === 5) {
            $validated['work_end_time'] .= ':00';
        }

        $office = OfficeSetting::getSetting();
        $office->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan lokasi kantor & jam absensi berhasil diperbarui.');
    }
}
