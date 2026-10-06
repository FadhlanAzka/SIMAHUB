<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $dayOfWeek = $now->dayOfWeekIso;

        $isWeekend = ($dayOfWeek >= 6);
        $office = OfficeSetting::getSetting();
        $wfhDays = is_array($office->wfh_days) ? $office->wfh_days : (json_decode($office->wfh_days, true) ?? []);
        $isWfhDay = in_array($dayOfWeek, $wfhDays);

        $internQuery = User::where('role', 'intern');
        if (auth()->user()->role === 'mentor') {
            $internQuery->where('mentor_id', auth()->id());
        }
        $totalintern = $internQuery->count();

        // Absensi hari ini
        $attendanceQuery = Attendance::with('user')->where('date', $today);
        if (auth()->user()->role === 'mentor') {
            $attendanceQuery->whereHas('user', function ($q) {
                $q->where('mentor_id', auth()->id());
            });
        }
        $todayAttendances = $attendanceQuery->get();

        $hadirCount = $todayAttendances->whereNotNull('check_in_time')->count();
        $tepatWaktuCount = $todayAttendances->where('status', 'tepat_waktu')->count();
        $terlambatCount = $todayAttendances->where('status', 'terlambat')->count();
        $wfhCount = $todayAttendances->where('type', 'WFH')->count();
        $wfoCount = $todayAttendances->where('type', 'WFO')->count();
        $belumHadirCount = max(0, $totalintern - $hadirCount);

        // Daftar absensi terbaru
        $recentQuery = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->limit(10);
        if (auth()->user()->role === 'mentor') {
            $recentQuery->whereHas('user', function ($q) {
                $q->where('mentor_id', auth()->id());
            });
        }
        $recentAttendances = $recentQuery->get();

        return view('admin.dashboard', compact(
            'now',
            'today',
            'isWeekend',
            'isWfhDay',
            'totalintern',
            'hadirCount',
            'tepatWaktuCount',
            'terlambatCount',
            'wfhCount',
            'wfoCount',
            'belumHadirCount',
            'todayAttendances',
            'recentAttendances',
            'office'
        ));
    }
}
