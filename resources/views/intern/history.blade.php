@extends('layouts.app')

@section('title', 'Riwayat Presensi Peserta Magang')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Riwayat Presensi Peserta Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan seluruh kehadiran Anda selama pelaksanaan program magang</p>
        </div>

        <!-- Filter Button -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(request()->anyFilled(['month', 'date', 'start_date', 'end_date', 'type']))
                <a href="{{ route('intern.history') }}" class="py-2.5 px-4 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    Reset Filter
                </a>
            @endif
            <button onclick="document.getElementById('filterModal').classList.remove('hidden')" class="py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 cursor-pointer inline-flex items-center gap-2">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                Filter Data
            </button>
        </div>
    </div>

    <!-- Monthly Statistics Widget -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Hadir</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $stats['total_hadir'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tepat Waktu</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['tepat_waktu'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terlambat</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['terlambat'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-rose-50/70 p-5 rounded-3xl border border-rose-200 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Akumulasi Terlambat</span>
            <div class="text-base sm:text-lg font-black text-rose-800 mt-1 font-mono leading-tight">{{ $stats['accumulated_late_text'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">WFH (Bebas Lokasi)</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['wfh'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                @if($selectedDate)
                    Data Kehadiran Tanggal {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                @elseif($startDate && $endDate)
                    Data Kehadiran {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                @elseif($selectedMonth)
                    Data Kehadiran Periode {{ \Carbon\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }}
                @else
                    Semua Data Kehadiran
                @endif
                @if($selectedType)
                    <span class="text-xs font-normal text-slate-500 ml-1">({{ $selectedType }})</span>
                @endif
            </h3>
            <span class="text-xs text-slate-500 font-medium">{{ $attendances->count() }} catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Hari</th>
                        <th class="px-6 py-3.5">Tipe</th>
                        <th class="px-6 py-3.5">Absen Masuk</th>
                        <th class="px-6 py-3.5">Status Masuk</th>
                        <th class="px-6 py-3.5">Absen Pulang</th>
                        <th class="px-6 py-3.5 relative group" style="cursor: help;">
                            <div class="flex items-center gap-1.5">
                                Durasi Kerja
                                <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                            </div>
                            <!-- Custom Tooltip (Bottom) -->
                            <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-max max-w-[200px] whitespace-normal px-2.5 py-1.5 bg-slate-800 text-white text-[10px] leading-relaxed font-medium rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 shadow-lg pointer-events-none text-center">
                                Total jam sudah dikurangi 1 jam waktu istirahat
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-slate-800"></div>
                            </div>
                        </th>
                        <th class="px-6 py-3.5 whitespace-nowrap">Total Durasi Terlambat</th>
                        <th class="px-6 py-3.5">Lokasi GPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($row->date)->translatedFormat('l') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $row->type_badge !!}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                                {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $row->status_badge !!}
                                @if($row->notes)
                                    <span class="block text-[11px] text-slate-500 mt-1 max-w-[150px] truncate" title="{{ $row->notes }}">
                                        {{ $row->notes }}
                                    </span>
                                @endif
                                @if($row->attachment)
                                    <div class="mt-1">
                                        <a href="{{ $row->attachment_url }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold hover:bg-rose-100 transition-colors" title="Lihat Surat Lampiran (PDF)">
                                            <i data-lucide="file-text" class="w-3 h-3 text-rose-600"></i>
                                            <span>Surat PDF</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                                {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap font-medium">
                                {{ $row->duration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-800">{{ $row->user_total_late_text ?? '0 Menit' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                @if($row->check_in_distance !== null)
                                    <span class="inline-flex items-center gap-1 font-mono text-[11px]">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        {{ $row->check_in_distance }}m
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Tidak ada data presensi pada bulan yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Filter -->
<div id="filterModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('filterModal').classList.add('hidden')"></div>
    
    <!-- Modal Content -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900">Filter Riwayat Presensi</h3>
            <button onclick="document.getElementById('filterModal').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        
        <form action="{{ route('intern.history') }}" method="GET" class="p-6 space-y-5" onsubmit="cleanEmptyInputs(this)">
            <!-- Tab Filter (Opsional, tapi kita tampilkan berjejer saja dengan label jelas) -->
            
            <div class="space-y-4">
                <!-- 1. Bulan Periode -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        1. Bulan Periode
                        <span class="text-[10px] text-slate-400 font-normal normal-case ml-1">(Format: YYYY-MM)</span>
                    </label>
                    <input type="month" name="month" value="{{ $selectedMonth }}" placeholder="YYYY-MM"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>
                
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="shrink-0 mx-4 text-slate-400 text-[10px] font-bold">ATAU</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <!-- 2. Tanggal Spesifik -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">2. Tanggal Spesifik</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="shrink-0 mx-4 text-slate-400 text-[10px] font-bold">ATAU</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <!-- 3. Tanggal Range -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">3. Rentang Tanggal</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="start_date" value="{{ $startDate }}" 
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                        <span class="text-slate-400 font-bold">-</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" 
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                    </div>
                </div>
                
                <div class="border-t border-slate-100 my-4 pt-4"></div>

                <!-- 4. WFO/WFH Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Metode Kerja (WFO/WFH)</label>
                    <select name="type" class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                        <option value="">Semua (WFO & WFH)</option>
                        <option value="WFO" {{ $selectedType === 'WFO' ? 'selected' : '' }}>WFO (Kantor)</option>
                        <option value="WFH" {{ $selectedType === 'WFH' ? 'selected' : '' }}>WFH (Rumah)</option>
                    </select>
                </div>
            </div>

            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('filterModal').classList.add('hidden')" class="py-2.5 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Clean empty inputs to keep URL clean (prevent ?month=&date=&start_date=...)
    function cleanEmptyInputs(form) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!input.value) {
                input.name = ''; 
            }
        });
    }
</script>

@endsection
