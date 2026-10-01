@extends('layouts.app')

@section('title', 'Edit Data intern')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.intern.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 inline-flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar intern
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
        <div class="pb-5 border-b border-slate-100 mb-6">
            <h1 class="text-lg font-bold text-slate-900">Perbarui Data intern</h1>
            <p class="text-xs text-slate-500 mt-0.5">Edit informasi profil untuk intern: <strong>{{ $intern->name }}</strong></p>
        </div>

        <form action="{{ route('admin.intern.update', $intern->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nama intern -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Lengkap intern <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $intern->name) }}" 
                           required 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- Username -->
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

                <!-- Posisi Magang -->
                <div>
                    <label for="position" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Posisi Magang
                    </label>
                    <input type="text" 
                           name="position" 
                           id="position" 
                           value="{{ old('position', $intern->position) }}" 
                           placeholder="Contoh: Web Developer / UI UX Designer"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>



                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        No. WhatsApp / HP
                    </label>
                    <input type="text" 
                           name="phone" 
                           id="phone" 
                           value="{{ old('phone', $intern->phone) }}" 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>

                <!-- Mentor Pendamping -->
                <div>
                    <label for="mentor_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Mentor Pendamping
                    </label>
                    <select name="mentor_id" 
                            id="mentor_id" 
                            class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                        <option value="">-- Pilih Mentor (Opsional) --</option>
                        @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}" {{ old('mentor_id', $intern->mentor_id) == $mentor->id ? 'selected' : '' }}>
                                {{ $mentor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password (Optional) -->
                <div class="sm:col-span-2">
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Ganti Kata Sandi (Kosongkan jika tidak diubah)
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="Masukkan kata sandi baru jika ingin mengubah"
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.intern.index') }}" class="py-2.5 px-4 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Perbarui Data
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
