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
        $selectedMonth = $request->get('month', $now->format('Y-m'));
        $selectedDate = $request->get('date');
        $selectedUserId = $request->get('user_id');
        $selectedStatus = $request->get('status');
        $selectedType = $request->get('type');

        $query = Attendance::with('user');

        if ($selectedDate) {
            $query->where('date', $selectedDate);
        } elseif ($selectedMonth) {
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();
            $query->whereBetween('date', [$startDate, $endDate]);
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

        $allintern = User::where('role', 'intern')->orderBy('name', 'asc')->get();

        return view('admin.attendance.index', compact(
            'attendances',
            'allintern',
            'selectedMonth',
            'selectedDate',
            'selectedUserId',
            'selectedStatus',
            'selectedType'
        ));
    }
}
