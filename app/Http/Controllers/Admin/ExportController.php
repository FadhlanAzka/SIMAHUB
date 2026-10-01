<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Download data absensi 1 bulan dalam format Spreadsheet (CSV/Excel siap olah).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $selectedMonth = $request->get('month');
        $selectedDate = $request->get('date');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $userId = $request->get('user_id');
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

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        $attendances = $query->orderBy('date', 'asc')
            ->orderBy('check_in_time', 'asc')
            ->get();

        $office = OfficeSetting::getSetting();
        
        $monthLabel = 'Semua Data';
        if ($selectedDate) {
            $monthLabel = Carbon::parse($selectedDate)->translatedFormat('d F Y');
        } elseif ($startDate && $endDate) {
            $monthLabel = Carbon::parse($startDate)->translatedFormat('d M Y') . ' sd ' . Carbon::parse($endDate)->translatedFormat('d M Y');
        } elseif ($selectedMonth) {
            $monthLabel = Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y');
        }

        $fileName = "Rekap_Presensi_SIMAHUB_" . str_replace(' ', '_', $monthLabel) . ".csv";

        $userLateTotals = [];
        $lateUsersCount = 0;
        foreach ($attendances as $row) {
            $uid = $row->user_id;
            if (!isset($userLateTotals[$uid])) {
                $userLateTotals[$uid] = 0;
            }
            $userLateTotals[$uid] += $row->late_minutes;
        }

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

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($attendances, $office, $monthLabel, $now, $userLateTotalsText, $userId) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Excel/Spreadsheets otomatis membaca karakter dengan rapi
            fputs($handle, "\xEF\xBB\xBF");

            // Header Dokumen Spreadsheet
            fputcsv($handle, ['REKAPITULASI PRESENSI PESERTA MAGANG - SIMAHUB']);
            fputcsv($handle, ['Instansi', $office->office_name]);
            fputcsv($handle, ['Periode Bulan', $monthLabel]);
            fputcsv($handle, ['Waktu Ekspor', $now->translatedFormat('d F Y, H:i') . ' WIB']);
            fputcsv($handle, ['Ketentuan Jam Kerja', 'Senin - Kamis (07:30 - 16:00 WIB), Jumat (07:30 - 16:30 WIB)']);
            fputcsv($handle, []); // Baris kosong pemisah

            // Header Kolom Tabel yang Tersinkronisasi dengan Sistem Terkini
            fputcsv($handle, [
                'No',
                'Username',
                'Nama Peserta Magang',
                'Posisi Magang',
                'No. WhatsApp / HP',
                'Tanggal',
                'Hari',
                'Metode Kerja',
                'Jam Masuk (WIB)',
                'Status Masuk',
                'Durasi Terlambat',
                'Jam Pulang (WIB)',
                'Durasi Kerja',
                'Total Durasi Terlambat',
                'Jarak Presensi (Meter)',
                'Catatan / Keterangan',
            ]);

            $no = 1;
            foreach ($attendances as $row) {
                $dayName = Carbon::parse($row->date)->translatedFormat('l');
                $lateDuration = $row->late_duration_text ?: '-';
                $statusText = ($row->status === 'tepat_waktu') 
                    ? 'Tepat Waktu' 
                    : (($row->status === 'terlambat') 
                        ? "TELAT\n(" . ($row->late_duration_text ?: '0 jam 0 Menit') . ")" 
                        : '-');
                $typeText = ($row->type === 'WFH') ? 'WFH (Rumah)' : 'WFO (Kantor)';
                $userTotalLate = $userLateTotalsText[$row->user_id] ?? '0 Menit';

                fputcsv($handle, [
                    $no++,
                    $row->user ? $row->user->username : '-',
                    $row->user ? $row->user->name : '-',
                    $row->user ? ($row->user->position ?: '-') : '-',
                    $row->user ? ($row->user->phone ?: '-') : '-',
                    $row->date->format('d/m/Y'),
                    $dayName,
                    $typeText,
                    $row->check_in_time ? substr($row->check_in_time, 0, 5) : '-',
                    $statusText,
                    $lateDuration,
                    $row->check_out_time ? substr($row->check_out_time, 0, 5) : '-',
                    $row->duration,
                    $userTotalLate,
                    $row->check_in_distance ? $row->check_in_distance . ' m' : '-',
                    $row->notes ?: '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Tampilan cetak / print PDF resmi laporan absensi bulanan.
     */
    public function printReport(Request $request)
    {
        $selectedMonth = $request->get('month');
        $selectedDate = $request->get('date');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $userId = $request->get('user_id');
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

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($selectedType) {
            $query->where('type', $selectedType);
        }

        $attendances = $query->orderBy('date', 'asc')
            ->orderBy('check_in_time', 'asc')
            ->get();

        $office = OfficeSetting::getSetting();
        
        $monthLabel = 'Semua Data';
        if ($selectedDate) {
            $monthLabel = Carbon::parse($selectedDate)->translatedFormat('d F Y');
        } elseif ($startDate && $endDate) {
            $monthLabel = Carbon::parse($startDate)->translatedFormat('d M Y') . ' sd ' . Carbon::parse($endDate)->translatedFormat('d M Y');
        } elseif ($selectedMonth) {
            $monthLabel = Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y');
        }

        $totalHadir = $attendances->count();
        $totalTepatWaktu = $attendances->where('status', 'tepat_waktu')->count();
        $totalTerlambat = $attendances->where('status', 'terlambat')->count();
        $totalWfh = $attendances->where('type', 'WFH')->count();
        $totalWfo = $attendances->where('type', 'WFO')->count();

        // Hitung akumulasi terlambat per peserta magang
        $userLateTotals = [];
        $lateUsersCount = 0;
        foreach ($attendances as $row) {
            $uid = $row->user_id;
            if (!isset($userLateTotals[$uid])) {
                $userLateTotals[$uid] = 0;
            }
            $userLateTotals[$uid] += $row->late_minutes;
        }

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

        // Pasangkan total durasi terlambat per peserta ke setiap baris data
        foreach ($attendances as $row) {
            $row->user_total_late_text = $userLateTotalsText[$row->user_id] ?? '0 Menit';
        }

        $accumulatedLateText = ($userId && isset($userLateTotalsText[$userId]))
            ? $userLateTotalsText[$userId]
            : null;

        return view('admin.export.report_print', compact(
            'attendances',
            'office',
            'selectedMonth',
            'monthLabel',
            'totalHadir',
            'totalTepatWaktu',
            'totalTerlambat',
            'totalWfh',
            'totalWfo',
            'lateUsersCount',
            'accumulatedLateText',
            'userId'
        ));
    }
}
