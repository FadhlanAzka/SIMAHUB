<?php

namespace App\Http\Controllers\intern;

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

        // Pengaturan kantor & jam kerja
        $office = OfficeSetting::getSetting();
        $wfhDays = array_values(array_map('intval', is_array($office->wfh_days) ? $office->wfh_days : (json_decode($office->wfh_days, true) ?? [])));

        $isWeekend = ($dayOfWeek >= 6); // Sabtu (6) atau Minggu (7)
        $isWfhDay = in_array((int) $dayOfWeek, $wfhDays, true);
        
        // Tipe kerja hari ini
        if ($isWeekend) {
            $workType = 'LIBUR';
        } elseif ($isWfhDay) {
            $workType = 'WFH';
        } else {
            $workType = 'WFO';
        }

        // Absensi hari ini untuk intern yang sedang login
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Batas jam kerja spesifik hari ini
        $workHours = is_array($office->work_hours) ? $office->work_hours : (json_decode($office->work_hours, true) ?? []);
        $todayStartLimit = $workHours[$dayOfWeek]['start'] ?? $workHours[(string)$dayOfWeek]['start'] ?? substr($office->work_start_time, 0, 5);
        $todayEndLimit = $workHours[$dayOfWeek]['end'] ?? $workHours[(string)$dayOfWeek]['end'] ?? substr($office->work_end_time, 0, 5);

        // Jadwal kerja & WFH mingguan untuk transparansi peserta
        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayDayName = $daysMap[$dayOfWeek] ?? 'Hari Ini';
        $weeklySchedule = [];
        foreach ([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'] as $dNum => $dName) {
            $dStart = $workHours[$dNum]['start'] ?? $workHours[(string)$dNum]['start'] ?? '07:30';
            $dEnd = $workHours[$dNum]['end'] ?? $workHours[(string)$dNum]['end'] ?? '16:00';
            $weeklySchedule[] = [
                'day_num' => $dNum,
                'day_name' => $dName,
                'is_today' => ($dayOfWeek == $dNum),
                'is_wfh' => in_array($dNum, $wfhDays, true),
                'start' => substr($dStart, 0, 5),
                'end' => substr($dEnd, 0, 5),
            ];
        }

        // Statistik bulan berjalan
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $totalLateMinutes = 0;
        foreach ($monthlyAttendances as $att) {
            $totalLateMinutes += $att->late_minutes;
        }
        $lateHours = (int) floor($totalLateMinutes / 60);
        $remLateMins = $totalLateMinutes % 60;
        $accumulatedLateText = $totalLateMinutes > 0 ? "{$lateHours} jam {$remLateMins} Menit" : "0 Menit";

        $stats = [
            'total_hadir' => $monthlyAttendances->count(),
            'tepat_waktu' => $monthlyAttendances->where('status', 'tepat_waktu')->count(),
            'terlambat' => $monthlyAttendances->where('status', 'terlambat')->count(),
            'wfh' => $monthlyAttendances->where('type', 'WFH')->count(),
            'total_late_minutes' => $totalLateMinutes,
            'accumulated_late_text' => $accumulatedLateText,
        ];

        // 5 riwayat absensi terakhir
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentAttendances as $att) {
            $att->user_total_late_text = $accumulatedLateText;
        }

        return view('intern.dashboard', compact(
            'user',
            'now',
            'today',
            'dayOfWeek',
            'isWeekend',
            'isWfhDay',
            'workType',
            'office',
            'todayAttendance',
            'stats',
            'recentAttendances',
            'todayStartLimit',
            'todayEndLimit',
            'todayDayName',
            'wfhDays',
            'weeklySchedule'
        ));
    }
}
