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
        $now = Carbon::now();
        $selectedMonth = $request->get('month', $now->format('Y-m'));
        $userId = $request->get('user_id');

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        $query = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('date', 'asc')
            ->orderBy('check_in_time', 'asc')
            ->get();

        $office = OfficeSetting::getSetting();
        $monthLabel = Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y');
        $fileName = "Rekap_Presensi_SIMAHUB_" . str_replace(' ', '_', $monthLabel) . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($attendances, $office, $monthLabel, $now) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Excel/Spreadsheets otomatis membaca karakter dengan rapi
            fputs($handle, "\xEF\xBB\xBF");

            // Header Dokumen Spreadsheet
            fputcsv($handle, ['REKAPITULASI PRESENSI MAHASISWA MAGANG - SIMAHUB']);
            fputcsv($handle, ['Instansi', $office->office_name]);
            fputcsv($handle, ['Periode Bulan', $monthLabel]);
            fputcsv($handle, ['Waktu Ekspor', $now->translatedFormat('d F Y, H:i') . ' WIB']);
            fputcsv($handle, ['Total Data', $attendances->count() . ' catatan presensi']);
            fputcsv($handle, []); // Baris kosong pemisah

            // Header Kolom Tabel
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Perguruan Tinggi / Asal Instansi',
                'Tanggal',
                'Hari',
                'Metode Kerja',
                'Jam Masuk (WIB)',
                'Status Masuk',
                'Jam Pulang (WIB)',
                'Durasi Kerja',
                'Jarak Presensi (Meter)',
                'Catatan / Keterangan',
            ]);

            $no = 1;
            foreach ($attendances as $row) {
                $dayName = Carbon::parse($row->date)->translatedFormat('l');
                $statusText = ($row->status === 'tepat_waktu') ? 'Tepat Waktu' : (($row->status === 'terlambat') ? 'Terlambat' : '-');
                $typeText = ($row->type === 'WFH') ? 'WFH (Rumah)' : 'WFO (Kantor)';

                fputcsv($handle, [
                    $no++,
                    $row->user ? $row->user->nim : '-',
                    $row->user ? $row->user->name : '-',
                    $row->user ? $row->user->institution : '-',
                    $row->date->format('d/m/Y'),
                    $dayName,
                    $typeText,
                    $row->check_in_time ? substr($row->check_in_time, 0, 5) : '-',
                    $statusText,
                    $row->check_out_time ? substr($row->check_out_time, 0, 5) : '-',
                    $row->duration,
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
        $now = Carbon::now();
        $selectedMonth = $request->get('month', $now->format('Y-m'));
        $userId = $request->get('user_id');

        $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        $query = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->orderBy('date', 'asc')
            ->orderBy('check_in_time', 'asc')
            ->get();

        $office = OfficeSetting::getSetting();
        $monthLabel = Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y');

        $totalHadir = $attendances->count();
        $totalTepatWaktu = $attendances->where('status', 'tepat_waktu')->count();
        $totalTerlambat = $attendances->where('status', 'terlambat')->count();
        $totalWfh = $attendances->where('type', 'WFH')->count();
        $totalWfo = $attendances->where('type', 'WFO')->count();

        return view('admin.export.report_print', compact(
            'attendances',
            'office',
            'selectedMonth',
            'monthLabel',
            'totalHadir',
            'totalTepatWaktu',
            'totalTerlambat',
            'totalWfh',
            'totalWfo'
        ));
    }
}
