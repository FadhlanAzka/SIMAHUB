<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $totalintern = User::where('role', 'intern')->count();

        // Absensi hari ini
        $todayAttendances = Attendance::with('user')
            ->where('date', $today)
            ->get();

        $hadirCount = $todayAttendances->whereNotNull('check_in_time')->count();
        $tepatWaktuCount = $todayAttendances->where('status', 'tepat_waktu')->count();
        $terlambatCount = $todayAttendances->where('status', 'terlambat')->count();
        $wfhCount = $todayAttendances->where('type', 'WFH')->count();
        $wfoCount = $todayAttendances->where('type', 'WFO')->count();
        $belumHadirCount = max(0, $totalintern - $hadirCount);

        // Daftar absensi terbaru
        $recentAttendances = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();

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
