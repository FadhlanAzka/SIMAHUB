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
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 min-h-screen">

    <!-- Floating Print Control Bar (Screen Only) -->
    <div class="no-print max-w-6xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-300 shadow-lg flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <i data-lucide="printer" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-800 block">Pratinjau Cetak / Dokumen PDF Laporan Presensi</span>
                <span class="text-[11px] text-slate-500">Format dioptimalkan untuk tata letak cetak A4 Landscape</span>
            </div>
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
    <div class="print-container max-w-6xl mx-auto bg-white p-6 sm:p-10 rounded-2xl sm:shadow-md border border-slate-200">
        
        <!-- Official Institutional Header (KOP INSTANSI) -->
        <div class="border-b-4 border-double border-slate-900 pb-4 mb-6 text-center">
            <div class="flex items-center justify-center gap-3.5 mb-1.5">
                <div class="w-12 h-12 rounded-xl bg-blue-700 text-white flex items-center justify-center font-bold text-xl shrink-0">
                    <i data-lucide="building-2" class="w-7 h-7"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-lg font-black tracking-wider text-slate-900 uppercase leading-snug">
                        {{ $office->office_name }}
                    </h1>
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-widest">
                        PROGRAM MAGANG &bull; SISTEM INFORMASI MAGANGHUB (SIMAHUB)
                    </h2>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Jadwal Kerja: Senin - Kamis (07:30 - 16:00 WIB) | Jumat (07:30 - 16:30 WIB)
                    </p>
                </div>
            </div>
        </div>

        <!-- Report Title & Metadata -->
        <div class="text-center my-6">
            <h3 class="text-base font-extrabold text-slate-900 uppercase tracking-wide">
                LAPORAN REKAPITULASI PRESENSI PESERTA MAGANG
            </h3>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                PERIODE BULAN: <span class="text-blue-700 font-bold uppercase">{{ $monthLabel }}</span>
            </p>
        </div>

        <!-- Metric Summary Chips (5 Kolom Termasuk Akumulasi Terlambat) -->
        <div class="grid grid-cols-5 gap-3 my-6 text-center text-xs">
            <div class="p-3 rounded-xl border border-slate-300 bg-slate-50/70">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Total Presensi</span>
                <span class="text-sm font-black text-slate-900 mt-0.5 block">{{ $totalHadir }} Catatan</span>
            </div>
            <div class="p-3 rounded-xl border border-slate-300 bg-slate-50/70">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Tepat Waktu</span>
                <span class="text-sm font-black text-emerald-700 mt-0.5 block">{{ $totalTepatWaktu }} Hari</span>
            </div>
            <div class="p-3 rounded-xl border border-slate-300 bg-slate-50/70">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Terlambat</span>
                <span class="text-sm font-black text-rose-700 mt-0.5 block">{{ $totalTerlambat }} Kali</span>
            </div>
            <div class="p-3 rounded-xl border {{ $accumulatedLateText ? 'border-rose-300 bg-rose-50/70' : 'border-amber-300 bg-amber-50/70' }}">
                <span class="text-[10px] {{ $accumulatedLateText ? 'text-rose-700' : 'text-amber-800' }} uppercase font-bold block">
                    {{ $accumulatedLateText ? 'Total Terlambat Peserta' : 'Peserta Terlambat' }}
                </span>
                <span class="text-xs font-black {{ $accumulatedLateText ? 'text-rose-800 font-mono' : 'text-amber-900' }} mt-0.5 block">
                    {{ $accumulatedLateText ?: ($lateUsersCount . ' Orang') }}
                </span>
            </div>
            <div class="p-3 rounded-xl border border-slate-300 bg-slate-50/70">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">WFH (Rumah)</span>
                <span class="text-sm font-black text-purple-700 mt-0.5 block">{{ $totalWfh }} Hari</span>
            </div>
        </div>

        <!-- Attendance Data Table (Proporsional & Estetis 100% Width) -->
        <table class="w-full text-left text-xs border border-slate-300 mt-4 table-fixed">
            <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 uppercase text-[10px]">
                <tr>
                    <th class="p-2.5 border-r border-slate-300 text-center w-[4%]">No</th>
                    <th class="p-2.5 border-r border-slate-300 w-[13%]">Tanggal</th>
                    <th class="p-2.5 border-r border-slate-300 w-[29%]">Peserta Magang</th>
                    <th class="p-2.5 border-r border-slate-300 text-center w-[9%]">Metode</th>
                    <th class="p-2.5 border-r border-slate-300 text-center w-[8%]">Masuk</th>
                    <th class="p-2.5 border-r border-slate-300 text-center w-[13%]">Status Masuk</th>
                    <th class="p-2.5 border-r border-slate-300 text-center w-[8%]">Pulang</th>
                    <th class="p-2.5 border-r border-slate-300 text-center w-[8%]">Durasi</th>
                    <th class="p-2.5 text-center w-[8%]">Total Telat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($attendances as $index => $row)
                    <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                        <td class="p-2.5 border-r border-slate-300 text-center font-mono text-slate-600 align-middle">
                            {{ $index + 1 }}
                        </td>
                        <td class="p-2.5 border-r border-slate-300 align-middle">
                            <span class="font-bold text-slate-800 block text-xs">{{ $row->date->format('d/m/Y') }}</span>
                            <span class="text-[10px] text-slate-500 font-medium block">({{ \Carbon\Carbon::parse($row->date)->translatedFormat('l') }})</span>
                        </td>
                        <td class="p-2.5 border-r border-slate-300 align-middle">
                            <div class="font-bold text-slate-900 leading-snug text-xs">
                                {{ $row->user ? $row->user->name : '-' }}
                            </div>
                            @if($row->user && $row->user->position)
                                <div class="text-[10px] text-slate-600 font-medium mt-0.5">
                                    {{ $row->user->position }}
                                </div>
                            @endif
                        </td>
                        <td class="p-2.5 border-r border-slate-300 text-center align-middle">
                            @if($row->type === 'WFH')
                                <span class="inline-block px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold text-[10px] border border-purple-200">
                                    WFH
                                </span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] border border-blue-200">
                                    WFO
                                </span>
                            @endif
                        </td>
                        <td class="p-2.5 border-r border-slate-300 font-mono text-center font-bold text-slate-800 align-middle">
                            {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) : '-' }}
                        </td>
                        <td class="p-2.5 border-r border-slate-300 text-center align-middle">
                            @if($row->status === 'tepat_waktu')
                                <span class="inline-block px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] border border-emerald-200">
                                    Tepat Waktu
                                </span>
                            @elseif($row->status === 'terlambat')
                                <div class="inline-block px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px] border border-rose-200 leading-tight">
                                    <span>TELAT</span>
                                    <span class="text-[9px] block font-mono font-normal">
                                        ({{ $row->late_duration_text ?: '0 jam 0 Menit' }})
                                    </span>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-2.5 border-r border-slate-300 font-mono text-center font-bold text-slate-800 align-middle">
                            {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '-' }}
                        </td>
                        <td class="p-2.5 border-r border-slate-300 text-center text-slate-700 font-medium align-middle">
                            {{ $row->duration }}
                        </td>
                        <td class="p-2.5 text-center font-mono font-bold text-slate-800 text-[10px] align-middle">
                            {{ $row->user_total_late_text ?? '0 Menit' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400 italic">
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
            <div class="text-center min-w-[220px]">
                <p class="font-medium text-slate-600">Mengetahui,</p>
                <p class="font-bold text-slate-900">Pembimbing / Admin Instansi</p>
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
