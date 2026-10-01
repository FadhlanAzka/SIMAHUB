@extends('layouts.app')

@section('title', 'Rekapitulasi Absensi Magang')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Tools -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Rekapitulasi Data Absensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring riwayat, perizinan, dan transaksi kehadiran seluruh peserta magang</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Filter Button -->
            <button onclick="document.getElementById('filterModal').classList.remove('hidden')" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                <i data-lucide="filter" class="w-4 h-4"></i>
                <span>Filter Data</span>
            </button>
            @if(request()->anyFilled(['month', 'date', 'start_date', 'end_date', 'user_id', 'status', 'type']))
                <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-all cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    <span>Reset</span>
                </a>
            @endif
            
            <!-- Button Catat Presensi / Izin Manual -->
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-md shadow-slate-800/10 transition-all cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Catat Manual</span>
            </button>

            <!-- Download Spreadsheet -->
            <a href="{{ route('admin.export.csv', request()->query()) }}" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>CSV</span>
            </a>

            <!-- Print PDF -->
            <a href="{{ route('admin.export.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-md shadow-slate-800/10 transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </a>
        </div>
    </div>

    <!-- Summary Statistics Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Presensi</span>
            <div class="text-xl font-black text-slate-900 mt-1">{{ $stats['total_hadir'] }} <span class="text-[10px] font-normal text-slate-500">Catatan</span></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tepat Waktu</span>
            <div class="text-xl font-black text-emerald-600 mt-1">{{ $stats['tepat_waktu'] }} <span class="text-[10px] font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terlambat</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ $stats['terlambat'] }} <span class="text-[10px] font-normal text-slate-500">Kali</span></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Izin & Sakit</span>
            <div class="text-xl font-black text-amber-600 mt-1">
                {{ ($stats['izin'] ?? 0) + ($stats['sakit'] ?? 0) }} 
                <span class="text-[10px] font-normal text-slate-500">({{ $stats['izin'] ?? 0 }} Izin, {{ $stats['sakit'] ?? 0 }} Sakit)</span>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs col-span-2 sm:col-span-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">WFH (Bebas Lokasi)</span>
            <div class="text-xl font-black text-purple-600 mt-1">{{ $stats['wfh'] }} <span class="text-[10px] font-normal text-slate-500">Hari</span></div>
        </div>
    </div>
    <!-- Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                @if($selectedDate)
                    Data Presensi Tanggal {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                @elseif($startDate && $endDate)
                    Data Presensi {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                @elseif($selectedMonth)
                    Data Presensi Periode {{ \Carbon\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }}
                @else
                    Semua Data Presensi
                @endif
                <span class="text-xs text-slate-500 font-medium ml-2">({{ $attendances->total() }} catatan ditemukan)</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Nama Peserta Magang</th>
                        <th class="px-5 py-3.5">Tipe</th>
                        <th class="px-5 py-3.5">Masuk</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Pulang</th>
                        <th class="px-5 py-3.5 relative group" style="cursor: help;">
                            <div class="flex items-center gap-1.5">
                                Durasi
                                <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                            </div>
                            <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-max max-w-[200px] whitespace-normal px-2.5 py-1.5 bg-slate-800 text-white text-[10px] leading-relaxed font-medium rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 shadow-lg pointer-events-none text-center">
                                Total jam sudah dikurangi 1 jam waktu istirahat
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-slate-800"></div>
                            </div>
                        </th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Total Durasi Terlambat</th>
                        <th class="px-5 py-3.5">Keterangan / Lokasi</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->date->format('d/m/Y') }}
                                <span class="text-[10px] text-slate-400 font-normal block">
                                    {{ \Carbon\Carbon::parse($row->date)->translatedFormat('l') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->user ? $row->user->name : '-' }}
                                @if($row->user && $row->user->position)
                                    <span class="text-[10px] text-blue-600 font-semibold block">{{ $row->user->position }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                {!! $row->type_badge !!}
                            </td>
                            <td class="px-5 py-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                                {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                {!! $row->status_badge !!}
                            </td>
                            <td class="px-5 py-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                                {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap font-medium">
                                {{ $row->duration }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-800 text-xs">{{ $row->user_total_late_text ?? '0 Menit' }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 max-w-[200px]">
                                @if($row->notes)
                                    <div class="text-[11px] font-medium text-slate-700 leading-snug truncate" title="{{ $row->notes }}">
                                        {{ $row->notes }}
                                    </div>
                                @endif
                                @if($row->check_in_distance !== null)
                                    <span class="font-mono text-[10px] text-slate-400 block mt-0.5">GPS: {{ $row->check_in_distance }}m</span>
                                @elseif(!$row->notes)
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            data-id="{{ $row->id }}"
                                            data-user="{{ $row->user ? $row->user->name : '-' }}"
                                            data-date="{{ $row->date->format('d/m/Y') }}"
                                            data-status="{{ $row->status }}"
                                            data-type="{{ $row->type }}"
                                            data-in="{{ $row->check_in_time ? substr($row->check_in_time, 0, 5) : '' }}"
                                            data-out="{{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '' }}"
                                            data-notes="{{ $row->notes ?? '' }}"
                                            onclick="openEditModalFromBtn(this)"
                                            title="Edit Presensi"
                                            class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors cursor-pointer">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.attendance.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan presensi ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Data" class="p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors cursor-pointer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Tidak ada data absensi yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Filter -->
<div id="filterModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('filterModal').classList.add('hidden')"></div>
    
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
            <h3 class="font-bold text-slate-900">Filter Rekapitulasi</h3>
            <button onclick="document.getElementById('filterModal').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.attendance.index') }}" method="GET" class="p-6 space-y-4 overflow-y-auto" onsubmit="cleanEmptyInputs(this)">
            
            <!-- intern Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih intern</label>
                <select name="user_id" class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                    <option value="">Semua intern</option>
                    @foreach($allintern as $m)
                        <option value="{{ $m->id }}" {{ $selectedUserId == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="border-t border-slate-100 my-2"></div>

            <!-- Bulan Periode -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Bulan Periode <span class="text-[10px] text-slate-400 font-normal normal-case ml-1">(Format: YYYY-MM)</span>
                </label>
                <input type="month" name="month" value="{{ $selectedMonth }}" placeholder="YYYY-MM"
                       class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
            </div>
            
            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="shrink-0 mx-4 text-slate-400 text-[10px] font-bold">ATAU</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>

            <!-- Tanggal Spesifik -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Spesifik</label>
                <input type="date" name="date" value="{{ $selectedDate }}" 
                       class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
            </div>

            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="shrink-0 mx-4 text-slate-400 text-[10px] font-bold">ATAU</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>

            <!-- Tanggal Range -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rentang Tanggal</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                    <span class="text-slate-400 font-bold">-</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>
            </div>
            
            <div class="border-t border-slate-100 my-2"></div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                        <option value="">Semua Status</option>
                        <option value="tepat_waktu" {{ $selectedStatus == 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
                        <option value="terlambat" {{ $selectedStatus == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="izin" {{ $selectedStatus == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ $selectedStatus == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="dispensasi" {{ $selectedStatus == 'dispensasi' ? 'selected' : '' }}>Dispensasi</option>
                        <option value="alpa" {{ $selectedStatus == 'alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
                
                <!-- WFO/WFH Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe</label>
                    <select name="type" class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                        <option value="">Semua Tipe</option>
                        <option value="WFO" {{ $selectedType === 'WFO' ? 'selected' : '' }}>WFO (Kantor)</option>
                        <option value="WFH" {{ $selectedType === 'WFH' ? 'selected' : '' }}>WFH (Rumah)</option>
                    </select>
                </div>
            </div>

            <div class="pt-5 mt-2 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="document.getElementById('filterModal').classList.add('hidden')" class="py-2.5 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 1: Catat Presensi / Izin Manual -->
<div id="createAttendanceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-all">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">Catat Presensi / Izin Manual</h3>
                    <p class="text-xs text-slate-500">Input kehadiran bagi yang lupa absen, izin, atau sakit</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('admin.attendance.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <!-- Pilih Peserta Magang -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Pilih Peserta Magang <span class="text-rose-500">*</span></label>
                <select name="user_id" required class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-medium">
                    <option value="">-- Pilih Peserta --</option>
                    @foreach($allintern as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->position ?: 'Peserta' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Presensi -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Presensi <span class="text-rose-500">*</span></label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-medium">
            </div>

            <!-- Status / Keterangan Kehadiran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Status Kehadiran <span class="text-rose-500">*</span></label>
                    <select name="status" id="createStatusSelect" onchange="toggleTimeInputs('create')" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-medium">
                        <option value="tepat_waktu">Hadir - Tepat Waktu</option>
                        <option value="terlambat">Hadir - Terlambat</option>
                        <option value="dispensasi">Dispensasi (Lupa Absen)</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit (Surat Dokter)</option>
                        <option value="alpa">Alpa (Tanpa Keterangan)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tipe Kerja</label>
                    <select name="type" id="createTypeSelect" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-medium">
                        <option value="WFO">WFO (Kantor)</option>
                        <option value="WFH">WFH (Rumah)</option>
                    </select>
                </div>
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-3" id="createTimeSection">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Jam Masuk</label>
                    <input type="time" name="check_in_time" value="07:30" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Jam Pulang</label>
                    <input type="time" name="check_out_time" value="16:00" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                </div>
            </div>

            <!-- Catatan / Keterangan -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Alasan / Catatan / Keterangan</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Surat sakit terlampir, izin keperluan kampus, atau lupa check-in pagi..." class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCreateModal()" class="py-2.5 px-4 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Presensi / Keterangan -->
<div id="editAttendanceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-all">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900">Edit Data Presensi</h3>
                    <p class="text-xs text-slate-500" id="editParticipantInfo">Koreksi jam atau status kehadiran</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editAttendanceForm" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <!-- Status / Keterangan Kehadiran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Status Kehadiran <span class="text-rose-500">*</span></label>
                    <select name="status" id="editStatusSelect" onchange="toggleTimeInputs('edit')" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-medium">
                        <option value="tepat_waktu">Hadir - Tepat Waktu</option>
                        <option value="terlambat">Hadir - Terlambat</option>
                        <option value="dispensasi">Dispensasi (Lupa Absen)</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tipe Kerja</label>
                    <select name="type" id="editTypeSelect" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-medium">
                        <option value="WFO">WFO (Kantor)</option>
                        <option value="WFH">WFH (Rumah)</option>
                    </select>
                </div>
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-3" id="editTimeSection">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Jam Masuk</label>
                    <input type="time" name="check_in_time" id="editCheckInTime" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Jam Pulang</label>
                    <input type="time" name="check_out_time" id="editCheckOutTime" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono">
                </div>
            </div>

            <!-- Catatan / Keterangan -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Alasan / Catatan / Keterangan</label>
                <textarea name="notes" id="editNotes" rows="2" placeholder="Contoh: Lupa absen pulang, surat izin, dispensasi tugas dinas luar..." class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditModal()" class="py-2.5 px-4 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-bold transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Perbarui Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cleanEmptyInputs(form) {
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!input.value) {
                input.name = ''; 
            }
        });
    }

    function openCreateModal() {
        document.getElementById('createAttendanceModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeCreateModal() {
        document.getElementById('createAttendanceModal').classList.add('hidden');
    }

    function openEditModalFromBtn(btn) {
        const data = {
            id: btn.dataset.id,
            user_name: btn.dataset.user,
            date_formatted: btn.dataset.date,
            status: btn.dataset.status,
            type: btn.dataset.type,
            check_in_time: btn.dataset.in,
            check_out_time: btn.dataset.out,
            notes: btn.dataset.notes
        };
        openEditModal(data);
    }

    function openEditModal(data) {
        const form = document.getElementById('editAttendanceForm');
        form.action = `/admin/attendance/${data.id}`;

        document.getElementById('editParticipantInfo').textContent = `${data.user_name} • Tanggal ${data.date_formatted}`;
        document.getElementById('editStatusSelect').value = data.status || 'tepat_waktu';
        document.getElementById('editTypeSelect').value = data.type || 'WFO';
        document.getElementById('editCheckInTime').value = data.check_in_time || '';
        document.getElementById('editCheckOutTime').value = data.check_out_time || '';
        document.getElementById('editNotes').value = data.notes || '';

        toggleTimeInputs('edit');

        document.getElementById('editAttendanceModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeEditModal() {
        document.getElementById('editAttendanceModal').classList.add('hidden');
    }

    function toggleTimeInputs(type) {
        const status = document.getElementById(type === 'create' ? 'createStatusSelect' : 'editStatusSelect').value;
        const timeSection = document.getElementById(type === 'create' ? 'createTimeSection' : 'editTimeSection');
        const typeSelect = document.getElementById(type === 'create' ? 'createTypeSelect' : 'editTypeSelect');

        if (['izin', 'sakit', 'alpa'].includes(status)) {
            timeSection.style.opacity = '0.5';
            typeSelect.disabled = true;
        } else {
            timeSection.style.opacity = '1';
            typeSelect.disabled = false;
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
@endsection
