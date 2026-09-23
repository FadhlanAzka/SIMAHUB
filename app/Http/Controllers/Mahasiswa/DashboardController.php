<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();
        $dayOfWeek = $now->dayOfWeekIso; // 1 (Senin) s/d 7 (Minggu)

        $isWeekend = ($dayOfWeek >= 6); // Sabtu (6) atau Minggu (7)
        $isFriday = ($dayOfWeek === 5); // Jumat (5)
        
        // Tipe kerja hari ini
        if ($isWeekend) {
            $workType = 'LIBUR';
        } elseif ($isFriday) {
            $workType = 'WFH';
        } else {
            $workType = 'WFO';
        }

        // Pengaturan kantor & jam kerja
        $office = OfficeSetting::getSetting();

        // Absensi hari ini untuk mahasiswa yang sedang login
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Statistik bulan berjalan
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $stats = [
            'total_hadir' => $monthlyAttendances->count(),
            'tepat_waktu' => $monthlyAttendances->where('status', 'tepat_waktu')->count(),
            'terlambat' => $monthlyAttendances->where('status', 'terlambat')->count(),
            'wfh' => $monthlyAttendances->where('type', 'WFH')->count(),
        ];

        // 5 riwayat absensi terakhir
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('mahasiswa.dashboard', compact(
            'user',
            'now',
            'today',
            'dayOfWeek',
            'isWeekend',
            'isFriday',
            'workType',
            'office',
            'todayAttendance',
            'stats',
            'recentAttendances'
        ));
    }
}
