@extends('layouts.app')

@section('title', 'Kelola Peserta Magang')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="shrink-0">
            <h1 class="text-xl font-bold text-slate-900">Manajemen Peserta Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun, data identitas, dan status peserta magang</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2.5 w-full">
            <!-- Search Form -->
            <form action="{{ route('admin.intern.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full xl:w-auto xl:justify-end">
                
                @if(auth()->user()->role === 'admin')
                <select name="mentor_id" class="w-full sm:w-40 text-xs py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800" onchange="this.form.submit()">
                    <option value="">Semua Mentor</option>
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}" {{ $mentorId == $mentor->id ? 'selected' : '' }}>{{ $mentor->name }}</option>
                    @endforeach
                </select>
                @endif

                <select name="position" class="w-full sm:w-40 text-xs py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800" onchange="this.form.submit()">
                    <option value="">Semua Posisi</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}" {{ $position == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                    @endforeach
                </select>

                <div class="relative w-full sm:w-40 shrink-0">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Cari nama..." 
                           class="text-xs py-2 pl-8 pr-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 w-full">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                </div>
                
                <button type="submit" class="sr-only"></button>
            </form>

            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.intern.create') }}" title="Tambah Peserta Magang" class="shrink-0 inline-flex items-center justify-center p-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all cursor-pointer">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
            </a>
            @endif
        </div>
    </div>

    <!-- Tabel Peserta Magang -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama Lengkap</th>
                        <th class="px-6 py-3.5">Posisi Magang</th>
                        <th class="px-6 py-3.5">Mentor</th>
                        <th class="px-6 py-3.5">No. Telepon / WA</th>
                        @if(auth()->user()->role === 'admin')
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($intern as $mhs)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                {{ $mhs->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    {{ $mhs->position ?: '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-700">
                                {{ $mhs->mentor ? $mhs->mentor->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($mhs->phone)
                                    @php
                                        // Bersihkan spasi/karakter non-digit lalu ganti awalan 0 menjadi 62
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $mhs->phone);
                                        $waNumber = preg_replace('/^0/', '62', $cleanPhone);
                                    @endphp
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        {{ $mhs->phone }}
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            @if(auth()->user()->role === 'admin')
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit -->
                                    <a href="{{ route('admin.intern.edit', $mhs->id) }}" 
                                       title="Edit Data"
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <!-- Reset Password -->
                                    <form action="{{ route('admin.intern.reset_password', $mhs->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Reset kata sandi peserta magang {{ $mhs->name }}?')">
                                        @csrf
                                        <button type="submit" 
                                                title="Reset Kata Sandi"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer">
                                            <i data-lucide="key" class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.intern.destroy', $mhs->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus peserta magang {{ $mhs->name }} beserta seluruh riwayat absensinya? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus Peserta Magang"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? '5' : '4' }}" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Belum ada peserta magang terdaftar. Silakan klik tombol <strong>Tambah Peserta Magang</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($intern->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $intern->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
