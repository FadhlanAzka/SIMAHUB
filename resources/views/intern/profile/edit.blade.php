@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
        <div class="pb-5 border-b border-slate-100 mb-6">
            <h1 class="text-lg font-bold text-slate-900">Profil Saya</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola informasi akun dan pengaturan login Anda.</p>
        </div>

        <form action="{{ route('intern.profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nama Peserta Magang (Readonly) -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Lengkap <span class="text-slate-400 font-normal normal-case ml-1">(Tidak dapat diubah)</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           value="{{ $intern->name }}" 
                           readonly 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl focus:outline-none cursor-not-allowed">
                </div>

                <!-- Username (Bisa diganti) -->
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Username <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="username" 
                           id="username" 
                           value="{{ old('username', $intern->username) }}" 
                           required 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- Posisi Magang (Readonly) -->
                <div>
                    <label for="position" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Posisi Magang <span class="text-slate-400 font-normal normal-case ml-1">(Tidak dapat diubah)</span>
                    </label>
                    <input type="text" 
                           id="position" 
                           value="{{ $intern->position ?: '-' }}" 
                           readonly
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl focus:outline-none cursor-not-allowed">
                </div>

                <!-- Phone (Readonly) -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        No. WhatsApp / HP <span class="text-slate-400 font-normal normal-case ml-1">(Tidak dapat diubah)</span>
                    </label>
                    <input type="text" 
                           id="phone" 
                           value="{{ $intern->phone ?: '-' }}" 
                           readonly
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl focus:outline-none cursor-not-allowed">
                </div>

                <!-- Mentor (Readonly) -->
                <div>
                    <label for="mentor" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Mentor Pendamping <span class="text-slate-400 font-normal normal-case ml-1">(Ditetapkan Admin)</span>
                    </label>
                    <input type="text" 
                           id="mentor" 
                           value="{{ $intern->mentor ? $intern->mentor->name : 'Belum ditentukan' }}" 
                           readonly
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-100 border border-slate-200 text-slate-500 rounded-xl focus:outline-none cursor-not-allowed">
                </div>

                <!-- Password (Optional) -->
                <div class="sm:col-span-2 mt-2">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Ganti Kata Sandi
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="Kosongkan jika tidak ingin mengubah kata sandi"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-5 mt-2 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="py-2.5 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
