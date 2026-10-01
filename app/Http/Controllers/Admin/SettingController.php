<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        $office = OfficeSetting::getSetting();
        return view('admin.settings.index', compact('office'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        $validated = $request->validate([
            'office_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'work_hours' => ['required', 'array'],
            'work_hours.*.start' => ['required', 'date_format:H:i'],
            'work_hours.*.end' => ['required', 'date_format:H:i'],
            'wfh_days' => ['nullable', 'array'],
            'wfh_days.*' => ['integer', 'between:1,7'],
        ], [
            'office_name.required' => 'Nama instansi / kantor wajib diisi.',
            'latitude.required' => 'Titik Latitude wajib diisi.',
            'longitude.required' => 'Titik Longitude wajib diisi.',
            'radius_meters.required' => 'Radius toleransi (meter) wajib diisi.',
            'work_hours.*.start.required' => 'Waktu mulai jam kerja wajib diisi.',
            'work_hours.*.end.required' => 'Waktu pulang jam kerja wajib diisi.',
        ]);

        foreach ($validated['work_hours'] as $day => $hours) {
            if (strlen($hours['start']) === 5) {
                $validated['work_hours'][$day]['start'] .= ':00';
            }
            if (strlen($hours['end']) === 5) {
                $validated['work_hours'][$day]['end'] .= ':00';
            }
        }

        // Pastikan wfh_days menjadi array of integers
        $rawWfhDays = $request->input('wfh_days', []);
        $validated['wfh_days'] = array_values(array_map('intval', (array) $rawWfhDays));

        // Selaraskan default work_start_time dan work_end_time
        if (isset($validated['work_hours'][1])) {
            $validated['work_start_time'] = $validated['work_hours'][1]['start'];
            $validated['work_end_time'] = $validated['work_hours'][1]['end'];
        }

        $office = OfficeSetting::getSetting();
        $office->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan lokasi kantor & jam absensi berhasil diperbarui.');
    }
}
