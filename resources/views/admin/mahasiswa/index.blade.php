@extends('layouts.app')

@section('title', 'Kelola Mahasiswa Magang')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manajemen Mahasiswa Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun, data identitas, dan status mahasiswa maganghub</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto">
            <!-- Search Form -->
            <form action="{{ route('admin.mahasiswa.index') }}" method="GET" class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari nama / NIM / kampus..." 
                       class="text-xs py-2 pl-8 pr-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 w-full sm:w-60">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
            </form>

            <a href="{{ route('admin.mahasiswa.create') }}" class="inline-flex items-center justify-center gap-2 py-2 px-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Mahasiswa</span>
            </a>
        </div>
    </div>

    <!-- Mahasiswa Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">NIM / ID</th>
                        <th class="px-6 py-3.5">Nama Lengkap</th>
                        <th class="px-6 py-3.5">Asal Kampus / Instansi</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">No. Telepon / WA</th>
                        <th class="px-6 py-3.5 text-center">Total Kehadiran</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mahasiswa as $mhs)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $mhs->nim ?: '-' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                {{ $mhs->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $mhs->institution ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $mhs->email }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap font-mono text-[11px]">
                                {{ $mhs->phone ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    {{ $mhs->attendances_count }} kali
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit -->
                                    <a href="{{ route('admin.mahasiswa.edit', $mhs->id) }}" 
                                       title="Edit Data"
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <!-- Reset Password -->
                                    <form action="{{ route('admin.mahasiswa.reset_password', $mhs->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Reset kata sandi mahasiswa {{ $mhs->name }}?')">
                                        @csrf
                                        <button type="submit" 
                                                title="Reset Kata Sandi"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer">
                                            <i data-lucide="key" class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.mahasiswa.destroy', $mhs->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus mahasiswa {{ $mhs->name }} beserta seluruh riwayat absensinya? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Hapus Mahasiswa"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Belum ada mahasiswa magang terdaftar. Silakan klik tombol <strong>Tambah Mahasiswa</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mahasiswa->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $mahasiswa->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
