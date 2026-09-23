@extends('layouts.app')

@section('title', 'Pengaturan Kantor & Jam Kerja')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <h1 class="text-xl font-bold text-slate-900">Pengaturan Instansi & Geofencing</h1>
        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi titik koordinat GPS kantor, radius toleransi absensi, dan jam kerja magang</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas Instansi -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-blue-600"></i>
                    Identitas Instansi
                </h3>
                <div>
                    <label for="office_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Instansi / Kantor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="office_name" 
                           id="office_name" 
                           value="{{ old('office_name', $office->office_name) }}" 
                           required 
                           class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 transition-all">
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Section 2: Titik Koordinat GPS & Radius -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-blue-600"></i>
                        Titik Koordinat & Radius Kantor (Geofencing WFO)
                    </h3>
                    <button type="button" 
                            id="btnGetCurrentLocation"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                        <span>Ambil Koordinat GPS Saat Ini</span>
                    </button>
                </div>

                <p class="text-xs text-slate-500 mb-4">
                    Koordinat ini digunakan sebagai titik acuan hitung jarak Haversine pada hari Senin - Kamis saat mahasiswa melakukan absensi WFO.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                            Latitude <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="latitude" 
                               id="latitude" 
                               value="{{ old('latitude', $office->latitude) }}" 
                               required 
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono transition-all">
                    </div>

                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                            Longitude <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="longitude" 
                               id="longitude" 
                               value="{{ old('longitude', $office->longitude) }}" 
                               required 
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono transition-all">
                    </div>

                    <div>
                        <label for="radius_meters" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                            Radius Toleransi (Meter) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               name="radius_meters" 
                               id="radius_meters" 
                               value="{{ old('radius_meters', $office->radius_meters) }}" 
                               required 
                               min="10" 
                               max="5000"
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono transition-all">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Section 3: Aturan Waktu Absensi -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i>
                    Batas Jam Presensi Masuk & Pulang
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="work_start_time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                            Batas Waktu Masuk (Format Jam:Menit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" 
                               name="work_start_time" 
                               id="work_start_time" 
                               value="{{ old('work_start_time', substr($office->work_start_time, 0, 5)) }}" 
                               required 
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono transition-all">
                        <span class="text-[11px] text-slate-500 mt-1 block">
                            Absen \(\le\) waktu ini berstatus <strong>Tepat Waktu</strong>, lewat dari waktu ini berstatus <strong>Terlambat</strong>.
                        </span>
                    </div>

                    <div>
                        <label for="work_end_time" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                            Waktu Mulai Absen Pulang <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" 
                               name="work_end_time" 
                               id="work_end_time" 
                               value="{{ old('work_end_time', substr($office->work_end_time, 0, 5)) }}" 
                               required 
                               class="w-full text-xs font-medium py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-900 font-mono transition-all">
                        <span class="text-[11px] text-slate-500 mt-1 block">
                            Mahasiswa hanya diizinkan absen pulang jika waktu sudah mencapai atau melewati jam ini.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="py-2.5 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    document.getElementById('btnGetCurrentLocation').addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Peramban Anda tidak mendukung Geolocation.');
            return;
        }

        const btn = this;
        btn.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i><span>Mendeteksi GPS...</span>';
        lucide.createIcons();

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('latitude').value = pos.coords.latitude.toFixed(8);
                document.getElementById('longitude').value = pos.coords.longitude.toFixed(8);
                btn.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i><span>Koordinat Berhasil Diambil!</span>';
                lucide.createIcons();
            },
            (err) => {
                alert('Gagal mendeteksi lokasi: ' + err.message);
                btn.innerHTML = '<i data-lucide="crosshair" class="w-3.5 h-3.5"></i><span>Ambil Koordinat GPS Saat Ini</span>';
                lucide.createIcons();
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });
</script>
@endpush
@endsection
