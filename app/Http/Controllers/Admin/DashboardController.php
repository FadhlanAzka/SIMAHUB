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
        $isFriday = ($dayOfWeek === 5);

        $totalMahasiswa = User::where('role', 'mahasiswa')->count();
        $office = OfficeSetting::getSetting();

        // Absensi hari ini
        $todayAttendances = Attendance::with('user')
            ->where('date', $today)
            ->get();

        $hadirCount = $todayAttendances->whereNotNull('check_in_time')->count();
        $tepatWaktuCount = $todayAttendances->where('status', 'tepat_waktu')->count();
        $terlambatCount = $todayAttendances->where('status', 'terlambat')->count();
        $wfhCount = $todayAttendances->where('type', 'WFH')->count();
        $wfoCount = $todayAttendances->where('type', 'WFO')->count();
        $belumHadirCount = max(0, $totalMahasiswa - $hadirCount);

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
            'isFriday',
            'totalMahasiswa',
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
