@extends('layouts.app')

@section('title', 'Tambah Mahasiswa Magang')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.mahasiswa.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Data Mahasiswa
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
        <div class="pb-5 border-b border-slate-100 mb-6">
            <h1 class="text-lg font-bold text-slate-900">Tambah Mahasiswa Magang Baru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun mahasiswa untuk mengakses sistem presensi SIMAHUB</p>
        </div>

        <form action="{{ route('admin.mahasiswa.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nama Mahasiswa -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Lengkap Mahasiswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}" 
                           required 
                           placeholder="Contoh: Moch. Akbar"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- NIM / Nomor Induk -->
                <div>
                    <label for="nim" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        NIM / ID Magang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nim" 
                           id="nim" 
                           value="{{ old('nim') }}" 
                           required 
                           placeholder="Contoh: 12345678"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           required 
                           placeholder="Contoh: akbar@kampus.ac.id"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- Asal Kampus / Instansi -->
                <div>
                    <label for="institution" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Asal Perguruan Tinggi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="institution" 
                           id="institution" 
                           value="{{ old('institution') }}" 
                           required 
                           placeholder="Contoh: Universitas Indonesia"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- No Telepon / WA -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        No. WhatsApp / HP
                    </label>
                    <input type="text" 
                           name="phone" 
                           id="phone" 
                           value="{{ old('phone') }}" 
                           placeholder="Contoh: 081234567890"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- Password -->
                <div class="sm:col-span-2">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Kata Sandi Awal <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required 
                           placeholder="Minimal 6 karakter"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                    <p class="text-[11px] text-slate-500 mt-1">Mahasiswa dapat login menggunakan Email atau NIM dengan kata sandi ini.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.mahasiswa.index') }}" class="py-2.5 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Simpan Mahasiswa
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
