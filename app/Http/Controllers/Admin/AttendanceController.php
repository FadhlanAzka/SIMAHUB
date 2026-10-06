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
            $monthStart = Carbon::parse($selectedMonth.'-01')->startOfMonth()->toDateString();
            $monthEnd = Carbon::parse($selectedMonth.'-01')->endOfMonth()->toDateString();
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

        // Hitung statistik dan total akumulasi keterlambatan untuk data yang sesuai filter
        $matchingQuery = clone $query;
        $matchingAttendances = $matchingQuery->get();

        $totalHadir = $matchingAttendances->count();
        $totalTepatWaktu = $matchingAttendances->where('status', 'tepat_waktu')->count();
        $totalTerlambat = $matchingAttendances->where('status', 'terlambat')->count();
        $totalIzin = $matchingAttendances->where('status', 'izin')->count();
        $totalSakit = $matchingAttendances->where('status', 'sakit')->count();
        $totalDispensasi = $matchingAttendances->where('status', 'dispensasi')->count();
        $totalWfh = $matchingAttendances->where('type', 'WFH')->count();

        // Hitung total keterlambatan per peserta magang untuk periode aktif
        $userLateTotals = [];
        $lateUsersCount = 0;
        $totalLateMinutes = 0;
        foreach ($matchingAttendances as $att) {
            $uid = $att->user_id;
            if (! isset($userLateTotals[$uid])) {
                $userLateTotals[$uid] = 0;
            }
            $userLateTotals[$uid] += $att->late_minutes;
            $totalLateMinutes += (int) $att->late_minutes;
        }

        $totalLateHours = (int) floor($totalLateMinutes / 60);
        $remLateMins = $totalLateMinutes % 60;

        $userLateTotalsText = [];
        foreach ($userLateTotals as $uid => $mins) {
            if ($mins > 0) {
                $lateUsersCount++;
                $h = (int) floor($mins / 60);
                $m = $mins % 60;
                $userLateTotalsText[$uid] = "{$h} jam {$m} Menit";
            } else {
                $userLateTotalsText[$uid] = '0 Menit';
            }
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Pasangkan total durasi terlambat per peserta ke setiap baris data
        $attendances->getCollection()->transform(function ($att) use ($userLateTotalsText) {
            $att->user_total_late_text = $userLateTotalsText[$att->user_id] ?? '0 Menit';

            return $att;
        });

        $stats = [
            'total_hadir' => $totalHadir,
            'tepat_waktu' => $totalTepatWaktu,
            'terlambat' => $totalTerlambat,
            'izin' => $totalIzin,
            'sakit' => $totalSakit,
            'dispensasi' => $totalDispensasi,
            'wfh' => $totalWfh,
            'late_users_count' => $lateUsersCount,
            'total_late_minutes' => $totalLateMinutes,
            'accumulated_late_text' => ($selectedUserId && isset($userLateTotalsText[$selectedUserId]))
                ? $userLateTotalsText[$selectedUserId]
                : ($totalLateMinutes > 0 ? "{$totalLateHours} jam {$remLateMins} Menit" : '0 Menit'),
        ];

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
            'selectedType',
            'stats'
        ));
    }

    /**
     * Catat presensi, izin, sakit, atau dispensasi lupa absen secara manual oleh Admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:tepat_waktu,terlambat,izin,sakit,dispensasi,alpa',
            'type' => 'nullable|in:WFO,WFH',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'notes' => 'nullable|string|max:500',
        ]);

        // Cek duplikasi presensi untuk user & tanggal tersebut
        $existing = Attendance::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Catatan presensi untuk peserta tersebut pada tanggal '.Carbon::parse($validated['date'])->translatedFormat('d F Y').' sudah ada. Silakan gunakan tombol edit pada baris tabel.');
        }

        $type = $validated['type'] ?? 'WFO';
        if (in_array($validated['status'], ['izin', 'sakit', 'alpa'])) {
            $type = 'WFO';
        }

        $notes = $validated['notes'] ?? null;
        if ($validated['status'] === 'dispensasi' && empty($notes)) {
            $notes = 'Dispensasi Lupa Absen oleh Admin';
        }

        Attendance::create([
            'user_id' => $validated['user_id'],
            'date' => $validated['date'],
            'status' => $validated['status'],
            'type' => $type,
            'check_in_time' => ! empty($validated['check_in_time']) ? Carbon::parse($validated['check_in_time'])->format('H:i:s') : null,
            'check_out_time' => ! empty($validated['check_out_time']) ? Carbon::parse($validated['check_out_time'])->format('H:i:s') : null,
            'notes' => $notes,
        ]);

        return redirect()->back()->with('success', 'Catatan presensi / perizinan berhasil ditambahkan.');
    }

    /**
     * Perbarui data presensi oleh Admin
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'required|in:tepat_waktu,terlambat,izin,sakit,dispensasi,alpa',
            'type' => 'nullable|in:WFO,WFH',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'notes' => 'nullable|string|max:500',
        ]);

        $type = $validated['type'] ?? $attendance->type;
        if (in_array($validated['status'], ['izin', 'sakit', 'alpa'])) {
            $type = 'WFO';
        }

        $notes = $validated['notes'] ?? $attendance->notes;
        if ($validated['status'] === 'dispensasi' && empty($notes) && empty($attendance->notes)) {
            $notes = 'Dispensasi Lupa Absen oleh Admin';
        }

        $attendance->update([
            'status' => $validated['status'],
            'type' => $type,
            'check_in_time' => ! empty($validated['check_in_time']) ? Carbon::parse($validated['check_in_time'])->format('H:i:s') : null,
            'check_out_time' => ! empty($validated['check_out_time']) ? Carbon::parse($validated['check_out_time'])->format('H:i:s') : null,
            'notes' => $notes,
        ]);

        return redirect()->back()->with('success', 'Data presensi berhasil diperbarui.');
    }

    /**
     * Hapus catatan presensi oleh Admin
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->back()->with('success', 'Catatan presensi berhasil dihapus.');
    }
}
