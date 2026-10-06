<?php

namespace App\Http\Controllers\intern;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Services\GeolocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Memproses Absen Masuk intern magang.
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString(); // HH:MM:SS
        $dayOfWeek = $now->dayOfWeekIso; // 1 = Senin, 5 = Jumat, 6 = Sabtu, 7 = Minggu

        $office = OfficeSetting::getSetting();
        $wfhDays = is_array($office->wfh_days) ? $office->wfh_days : (json_decode($office->wfh_days, true) ?? []);
        $isWfhDay = in_array($dayOfWeek, $wfhDays);

        $request->validate([
            'latitude' => [$isWfhDay ? 'nullable' : 'required', 'numeric'],
            'longitude' => [$isWfhDay ? 'nullable' : 'required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'latitude.required' => 'Koordinat GPS wajib diaktifkan pada peramban Anda untuk WFO.',
            'longitude.required' => 'Koordinat GPS wajib diaktifkan pada peramban Anda untuk WFO.',
        ]);

        // 1. Validasi Hari Kerja (Senin - Jumat)
        if ($dayOfWeek >= 6) {
            return back()->with('error', 'Hari Sabtu dan Minggu adalah hari libur magang. Anda tidak dapat melakukan absensi pada akhir pekan.');
        }

        // 2. Cek apakah sudah absen hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->check_in_time) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini pada pukul '.substr($attendance->check_in_time, 0, 5).' WIB.');
        }

        $userLat = $request->latitude ? (float) $request->latitude : null;
        $userLong = $request->longitude ? (float) $request->longitude : null;
        $distance = null;

        // Hitung jarak pengguna ke kantor instansi dalam meter (Jika ada koordinat)
        if ($userLat && $userLong) {
            $distance = GeolocationService::calculateDistance(
                $userLat,
                $userLong,
                (float) $office->latitude,
                (float) $office->longitude
            );
        }

        // 3. Kebijakan WFO vs WFH
        // WFH: Berdasarkan pengaturan WFH yang dipilih Admin (Bebas dari rumah / bypass radius)
        // WFO: Selain hari WFH (Wajib di lingkungan instansi / radius)
        $wfhDays = is_array($office->wfh_days) ? $office->wfh_days : (json_decode($office->wfh_days, true) ?? []);
        $isWfhDay = in_array($dayOfWeek, $wfhDays);

        if ($isWfhDay) {
            $type = 'WFH';
        } else {
            $type = 'WFO';
            // Validasi Geofencing jika hari WFO
            if ($distance > $office->radius_meters) {
                return back()->with(
                    'error',
                    "Absensi ditolak! Pada hari kerja normal (WFO), absensi wajib di lingkungan instansi. Anda terdeteksi berada di luar area kantor ({$distance} meter dari kantor, batas toleransi: {$office->radius_meters} meter)."
                );
            }
        }

        $workHours = is_array($office->work_hours) ? $office->work_hours : (json_decode($office->work_hours, true) ?? []);
        $startLimit = $workHours[$dayOfWeek]['start'] ?? $office->work_start_time;

        // 4. Penentuan Status Masuk
        // Jika <= jam masuk -> Tepat Waktu; Jika > jam masuk -> Terlambat
        $status = ($currentTime <= $startLimit) ? 'tepat_waktu' : 'terlambat';

        // 5. Simpan Transaksi Absensi
        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'check_in_time' => $currentTime,
                'status' => $status,
                'type' => $type,
                'check_in_lat' => $userLat,
                'check_in_long' => $userLong,
                'check_in_distance' => $distance,
                'notes' => $request->notes,
            ]
        );

        $statusLabel = ($status === 'tepat_waktu') ? 'Tepat Waktu' : 'Terlambat';
        $typeLabel = ($type === 'WFH') ? 'WFH (Rumah)' : 'WFO (Kantor)';

        return back()->with(
            'success',
            "Berhasil melakukan Absen Masuk ({$statusLabel} - {$typeLabel}) pada pukul ".substr($currentTime, 0, 5).' WIB.'
        );
    }

    /**
     * Memproses Absen Pulang intern magang.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString(); // HH:MM:SS
        $dayOfWeek = $now->dayOfWeekIso;

        // 1. Validasi Hari Kerja
        if ($dayOfWeek >= 6) {
            return back()->with('error', 'Hari Sabtu dan Minggu adalah hari libur magang.');
        }

        // 2. Cek apakah sudah absen masuk
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in_time) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini. Silakan absen masuk terlebih dahulu.');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'Anda sudah melakukan absen pulang hari ini pada pukul '.substr($attendance->check_out_time, 0, 5).' WIB.');
        }

        // 3. Validasi Waktu Pulang
        $office = OfficeSetting::getSetting();
        $workHours = is_array($office->work_hours) ? $office->work_hours : (json_decode($office->work_hours, true) ?? []);
        $endLimit = $workHours[$dayOfWeek]['end'] ?? $office->work_end_time;
        if (strlen($endLimit) === 5) {
            $endLimit .= ':00';
        }

        if ($currentTime < $endLimit) {
            return back()->with(
                'error',
                'Belum waktunya pulang! Absen pulang hanya dapat dilakukan mulai pukul '.substr($endLimit, 0, 5).' WIB (Waktu saat ini: '.substr($currentTime, 0, 5).' WIB).'
            );
        }

        // Hitung jarak saat checkout jika koordinat tersedia
        $distance = null;
        if ($request->latitude && $request->longitude) {
            $distance = GeolocationService::calculateDistance(
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $office->latitude,
                (float) $office->longitude
            );
        }

        $attendance->update([
            'check_out_time' => $currentTime,
            'check_out_lat' => $request->latitude,
            'check_out_long' => $request->longitude,
            'check_out_distance' => $distance,
        ]);

        return back()->with(
            'success',
            'Berhasil melakukan Absen Pulang pada pukul '.substr($currentTime, 0, 5).' WIB. Terima kasih atas kerja keras Anda hari ini!'
        );
    }

    /**
     * Menampilkan riwayat absensi intern magang.
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $selectedMonth = $request->get('month');
        $selectedDate = $request->get('date');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $selectedType = $request->get('type');

        $query = Attendance::where('user_id', $user->id);

        if ($selectedDate) {
            $query->where('date', $selectedDate);
        } elseif ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($selectedMonth) {
            $monthStart = Carbon::parse($selectedMonth.'-01')->startOfMonth()->toDateString();
            $monthEnd = Carbon::parse($selectedMonth.'-01')->endOfMonth()->toDateString();
            $query->whereBetween('date', [$monthStart, $monthEnd]);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $totalLateMinutes = 0;
        foreach ($attendances as $att) {
            $totalLateMinutes += $att->late_minutes;
        }

        $lateHours = (int) floor($totalLateMinutes / 60);
        $remLateMins = $totalLateMinutes % 60;
        $accumulatedLateText = $totalLateMinutes > 0 ? "{$lateHours} jam {$remLateMins} Menit" : '0 Menit';

        foreach ($attendances as $att) {
            $att->user_total_late_text = $accumulatedLateText;
        }

        $stats = [
            'total_hadir' => $attendances->count(),
            'tepat_waktu' => $attendances->where('status', 'tepat_waktu')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'wfh' => $attendances->where('type', 'WFH')->count(),
            'total_late_minutes' => $totalLateMinutes,
            'accumulated_late_text' => $accumulatedLateText,
        ];

        return view('intern.history', compact('user', 'attendances', 'selectedMonth', 'selectedDate', 'startDate', 'endDate', 'selectedType', 'stats'));
    }
}
