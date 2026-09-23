<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Magang - {{ $monthLabel }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 min-h-screen">

    <!-- Floating Print Control Bar (Screen Only) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-300 shadow-lg flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="printer" class="w-5 h-5 text-blue-600"></i>
            <span class="text-xs font-bold text-slate-800">Pratinjau Cetak / PDF Laporan Presensi</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="py-2 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Official Paper Document Container -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-xl sm:shadow-md border border-slate-200">
        
        <!-- Official Institutional Header (KOP INSTANSI) -->
        <div class="border-b-4 border-double border-slate-900 pb-4 mb-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-1">
                <div class="w-12 h-12 rounded-xl bg-blue-700 text-white flex items-center justify-center font-bold text-xl">
                    <i data-lucide="building-2" class="w-7 h-7"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-lg font-black tracking-wider text-slate-900 uppercase">
                        {{ $office->office_name }}
                    </h1>
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-widest">
                        PROGRAM MAGANG MAHASISWA &bull; SISTEM INFORMASI MAGANGHUB (SIMAHUB)
                    </h2>
                    <p class="text-[10px] text-slate-500 font-medium">
                        Wilayah Presensi: Radius {{ $office->radius_meters }}m Kantor | Jadwal Kerja: 07:30 - 16:00 WIB
                    </p>
                </div>
            </div>
        </div>

        <!-- Report Title & Metadata -->
        <div class="text-center my-6">
            <h3 class="text-base font-extrabold text-slate-900 uppercase tracking-wide">
                LAPORAN REKAPITULASI PRESENSI MAHASISWA MAGANG
            </h3>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                PERIODE BULAN: <span class="text-blue-700 font-bold uppercase">{{ $monthLabel }}</span>
            </p>
        </div>

        <!-- Metric Summary Chips -->
        <div class="grid grid-cols-4 gap-3 my-6 text-center text-xs">
            <div class="p-3 rounded-lg border border-slate-300 bg-slate-50">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Total Presensi</span>
                <span class="text-base font-black text-slate-900">{{ $totalHadir }} Hari</span>
            </div>
            <div class="p-3 rounded-lg border border-slate-300 bg-slate-50">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Tepat Waktu</span>
                <span class="text-base font-black text-emerald-700">{{ $totalTepatWaktu }} Hari</span>
            </div>
            <div class="p-3 rounded-lg border border-slate-300 bg-slate-50">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Terlambat</span>
                <span class="text-base font-black text-rose-700">{{ $totalTerlambat }} Hari</span>
            </div>
            <div class="p-3 rounded-lg border border-slate-300 bg-slate-50">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">WFH (Jumat)</span>
                <span class="text-base font-black text-purple-700">{{ $totalWfh }} Hari</span>
            </div>
        </div>

        <!-- Attendance Data Table -->
        <table class="w-full text-left text-xs border border-slate-300 mt-4">
            <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 uppercase text-[10px]">
                <tr>
                    <th class="p-2 border-r border-slate-300 text-center w-8">No</th>
                    <th class="p-2 border-r border-slate-300">Tanggal</th>
                    <th class="p-2 border-r border-slate-300">NIM</th>
                    <th class="p-2 border-r border-slate-300">Nama Mahasiswa</th>
                    <th class="p-2 border-r border-slate-300">Tipe</th>
                    <th class="p-2 border-r border-slate-300 text-center">Masuk</th>
                    <th class="p-2 border-r border-slate-300 text-center">Status</th>
                    <th class="p-2 border-r border-slate-300 text-center">Pulang</th>
                    <th class="p-2 text-center">Durasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($attendances as $index => $row)
                    <tr>
                        <td class="p-2 border-r border-slate-300 text-center font-mono">{{ $index + 1 }}</td>
                        <td class="p-2 border-r border-slate-300 font-medium whitespace-nowrap">
                            {{ $row->date->format('d/m/Y') }}
                            <span class="text-[10px] text-slate-500 block">({{ \Carbon\Carbon::parse($row->date)->translatedFormat('l') }})</span>
                        </td>
                        <td class="p-2 border-r border-slate-300 font-mono text-slate-700 whitespace-nowrap">
                            {{ $row->user ? $row->user->nim : '-' }}
                        </td>
                        <td class="p-2 border-r border-slate-300 font-semibold text-slate-900 whitespace-nowrap">
                            {{ $row->user ? $row->user->name : '-' }}
                            <span class="text-[10px] text-slate-500 font-normal block">{{ $row->user ? $row->user->institution : '' }}</span>
                        </td>
                        <td class="p-2 border-r border-slate-300 font-bold text-[10px] whitespace-nowrap">
                            {{ $row->type === 'WFH' ? 'WFH (Rumah)' : 'WFO (Kantor)' }}
                        </td>
                        <td class="p-2 border-r border-slate-300 font-mono text-center whitespace-nowrap">
                            {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) : '-' }}
                        </td>
                        <td class="p-2 border-r border-slate-300 text-center font-semibold whitespace-nowrap text-[10px]">
                            @if($row->status === 'tepat_waktu')
                                <span class="text-emerald-700">Tepat Waktu</span>
                            @elseif($row->status === 'terlambat')
                                <span class="text-rose-700">Terlambat</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-2 border-r border-slate-300 font-mono text-center whitespace-nowrap">
                            {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '-' }}
                        </td>
                        <td class="p-2 text-center text-slate-700 whitespace-nowrap font-medium">
                            {{ $row->duration }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-slate-400 italic">
                            Tidak ada catatan absensi pada periode bulan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures Block -->
        <div class="mt-12 pt-4 flex justify-between items-start text-xs text-slate-800">
            <div>
                <p class="text-[11px] text-slate-500">Dicetak melalui sistem SIMAHUB</p>
                <p class="text-[11px] text-slate-500">Tanggal cetak: {{ date('d F Y, H:i') }} WIB</p>
            </div>
            <div class="text-center min-w-[200px]">
                <p class="font-medium">Mengetahui,</p>
                <p class="font-bold">Pembimbing / Admin Instansi</p>
                <div class="h-20"></div>
                <p class="font-extrabold underline text-slate-900 uppercase">ADMINISTRATOR</p>
                <p class="text-[11px] text-slate-500">{{ $office->office_name }}</p>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
