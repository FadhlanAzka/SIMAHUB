<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $selectedMonth = $request->get('month');
        $selectedDate = $request->get('date');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $selectedUserId = $request->get('user_id');
        $selectedStatus = $request->get('status');
        $selectedType = $request->get('type');

        $query = Attendance::with('user');
        
        if (auth()->user()->role === 'mentor') {
            $query->whereHas('user', function ($q) {
                $q->where('mentor_id', auth()->id());
            });
        }

        if ($selectedDate) {
            $query->where('date', $selectedDate);
        } elseif ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($selectedMonth) {
            $monthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
            $monthEnd = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();
            $query->whereBetween('date', [$monthStart, $monthEnd]);
        }

        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20)
            ->withQueryString();

        $internQuery = User::where('role', 'intern')->orderBy('name', 'asc');
        
        if (auth()->user()->role === 'mentor') {
            $internQuery->where('mentor_id', auth()->id());
        }
        
        $allintern = $internQuery->get();

        return view('admin.attendance.index', compact(
            'attendances',
            'allintern',
            'selectedMonth',
            'selectedDate',
            'startDate',
            'endDate',
            'selectedUserId',
            'selectedStatus',
            'selectedType'
        ));
    }
}
