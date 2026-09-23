<?php

namespace App\Http\Controllers\Mahasiswa;

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
     * Memproses Absen Masuk mahasiswa magang.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'latitude.required' => 'Koordinat GPS wajib diaktifkan pada peramban Anda.',
            'longitude.required' => 'Koordinat GPS wajib diaktifkan pada peramban Anda.',
        ]);

        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString(); // HH:MM:SS
        $dayOfWeek = $now->dayOfWeekIso; // 1 = Senin, 5 = Jumat, 6 = Sabtu, 7 = Minggu

        // 1. Validasi Hari Kerja (Senin - Jumat)
        if ($dayOfWeek >= 6) {
            return back()->with('error', 'Hari Sabtu dan Minggu adalah hari libur magang. Anda tidak dapat melakukan absensi pada akhir pekan.');
        }

        // 2. Cek apakah sudah absen hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->check_in_time) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini pada pukul ' . substr($attendance->check_in_time, 0, 5) . ' WIB.');
        }

        $office = OfficeSetting::getSetting();
        $userLat = (float) $request->latitude;
        $userLong = (float) $request->longitude;

        // Hitung jarak pengguna ke kantor instansi dalam meter
        $distance = GeolocationService::calculateDistance(
            $userLat,
            $userLong,
            (float) $office->latitude,
            (float) $office->longitude
        );

        // 3. Kebijakan WFO vs WFH
        // Jumat = WFH (Bebas dari rumah / bypass radius)
        // Senin - Kamis = WFO (Wajib di lingkungan instansi / radius)
        $isFriday = ($dayOfWeek === 5);

        if ($isFriday) {
            $type = 'WFH';
        } else {
            $type = 'WFO';
            // Validasi Geofencing jika hari Senin - Kamis
            if ($distance > $office->radius_meters) {
                return back()->with(
                    'error',
                    "Absensi ditolak! Pada hari Senin s/d Kamis absensi wajib WFO di lingkungan instansi. Anda terdeteksi berada di luar area kantor ({$distance} meter dari kantor, batas toleransi: {$office->radius_meters} meter)."
                );
            }
        }

        // 4. Penentuan Status Masuk (Batas 07:30:00)
        // Jika <= 07:30 -> Tepat Waktu; Jika > 07:30 -> Terlambat
        $status = ($currentTime <= $office->work_start_time) ? 'tepat_waktu' : 'terlambat';

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
            "Berhasil melakukan Absen Masuk ({$statusLabel} - {$typeLabel}) pada pukul " . substr($currentTime, 0, 5) . " WIB."
        );
    }

    /**
     * Memproses Absen Pulang mahasiswa magang.
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

        if (!$attendance || !$attendance->check_in_time) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini. Silakan absen masuk terlebih dahulu.');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'Anda sudah melakukan absen pulang hari ini pada pukul ' . substr($attendance->check_out_time, 0, 5) . ' WIB.');
        }

        // 3. Validasi Waktu Pulang (Minimal 16:00:00)
        $office = OfficeSetting::getSetting();
        if ($currentTime < $office->work_end_time) {
            return back()->with(
                'error',
                "Belum waktunya pulang! Absen pulang hanya dapat dilakukan mulai pukul " . substr($office->work_end_time, 0, 5) . " WIB (Waktu saat ini: " . substr($currentTime, 0, 5) . " WIB)."
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
            "Berhasil melakukan Absen Pulang pada pukul " . substr($currentTime, 0, 5) . " WIB. Terima kasih atas kerja keras Anda hari ini!"
        );
    }

    /**
     * Menampilkan riwayat absensi mahasiswa magang.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $selectedMonth = $request->get('month', $now->format('Y-m'));
        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $stats = [
            'total_hadir' => $attendances->count(),
            'tepat_waktu' => $attendances->where('status', 'tepat_waktu')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'wfh' => $attendances->where('type', 'WFH')->count(),
        ];

        return view('mahasiswa.history', compact('user', 'attendances', 'selectedMonth', 'stats'));
    }
}
