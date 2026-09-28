@extends('layouts.app')

@section('title', 'Pengaturan Lokasi & Jam Presensi')

@push('styles')
<!-- Leaflet CSS for Visual Interactive Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #mapContainer {
        height: 380px;
        z-index: 10;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <h1 class="text-xl font-black text-slate-900 tracking-tight">Pengaturan Instansi & Visual Geofencing</h1>
        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi visual titik koordinat peta GPS kantor, radius toleransi absensi, dan jam kerja magang</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas Instansi -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    </div>
                    <span>Identitas Instansi</span>
                </h3>
                <div>
                    <label for="office_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
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

            <!-- Section 2: Visual Map Geofencing -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="map" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>Peta Interaktif Geofencing Kantor (WFO)</span>
                    </h3>
                    <button type="button" 
                            id="btnGetCurrentLocation"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100/70 border border-blue-200/60 transition-colors cursor-pointer w-fit">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                        <span>Pusatkan ke Lokasi GPS Saya</span>
                    </button>
                </div>

                <p class="text-xs text-slate-500 mb-3">
                    Klik di area peta mana saja atau geser pin marker biru untuk menentukan lokasi instansi Anda. Lingkaran biru menandakan zona toleransi absensi WFO intern.
                </p>

                <!-- Interactive Visual Map Container -->
                <div class="rounded-2xl overflow-hidden border border-slate-300 shadow-inner mb-4 relative">
                    <div id="mapContainer" class="w-full"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
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
                        <label for="longitude" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
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
                        <label for="radius_meters" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
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

            <!-- Section 3: Aturan Waktu Absensi Per Hari -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    </div>
                    <span>Batas Jam Presensi Masuk & Pulang (Per Hari)</span>
                </h3>

                @php
                    $daysMap = [
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                    ];
                    $workHours = is_array($office->work_hours) ? $office->work_hours : (json_decode($office->work_hours, true) ?? []);
                @endphp

                <div class="space-y-3">
                    @foreach($daysMap as $val => $dayName)
                        @php
                            $start = $workHours[$val]['start'] ?? substr($office->work_start_time, 0, 5);
                            $end = $workHours[$val]['end'] ?? substr($office->work_end_time, 0, 5);
                        @endphp
                        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center p-4 rounded-xl border border-slate-200/60 bg-slate-50/50">
                            <div class="w-full sm:w-1/4 text-sm font-bold text-slate-800">{{ $dayName }}</div>
                            <div class="w-full sm:w-3/4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Masuk (Maksimal)</label>
                                    <input type="time" name="work_hours[{{ $val }}][start]" value="{{ substr($start, 0, 5) }}" required class="w-full text-xs font-medium py-2 px-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-mono transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pulang (Minimal)</label>
                                    <input type="time" name="work_hours[{{ $val }}][end]" value="{{ substr($end, 0, 5) }}" required class="w-full text-xs font-medium py-2 px-3 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-mono transition-all">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <span class="text-[11px] text-slate-500 mt-3 block">
                    Absen masuk \(\le\) jam masuk berstatus <strong>Tepat Waktu</strong>. intern hanya dapat absen pulang jika waktu sudah mencapai jam pulang.
                </span>
            </div>

            <hr class="border-slate-100">

            <!-- Section 4: Pengaturan Hari WFH -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    </div>
                    <span>Pengaturan Hari WFH (Bebas Lokasi)</span>
                </h3>
                
                <p class="text-xs text-slate-500 mb-3">
                    Pilih hari apa saja yang diizinkan untuk Work From Home (WFH). Pada hari yang dipilih, intern dapat melakukan absen dari mana saja (validasi radius lokasi ditiadakan).
                </p>

                @php
                    $wfhDays = old('wfh_days', is_array($office->wfh_days) ? $office->wfh_days : json_decode($office->wfh_days, true) ?? []);
                    $days = [
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                    ];
                @endphp

                <div class="flex flex-wrap gap-4 mt-2">
                    @foreach($days as $val => $dayName)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="wfh_days[]" value="{{ $val }}" 
                                {{ in_array($val, $wfhDays) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-600 transition-colors">
                                {{ $dayName }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<!-- Leaflet JS for Map -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');
        const radiusInput = document.getElementById('radius_meters');

        let initialLat = parseFloat(latInput.value) || -6.1753924;
        let initialLon = parseFloat(lonInput.value) || 106.8271528;
        let initialRadius = parseInt(radiusInput.value) || 100;

        // Initialize Map
        const map = L.map('mapContainer').setView([initialLat, initialLon], 16);

        // OpenStreetMap Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Custom Marker
        let marker = L.marker([initialLat, initialLon], {
            draggable: true
        }).addTo(map);

        marker.bindPopup("<strong class='text-xs'>Titik Kantor Instansi</strong><br><span class='text-[10px] text-slate-500'>Geser marker atau klik peta untuk memindahkan</span>").openPopup();

        // Circle for Radius
        let circle = L.circle([initialLat, initialLon], {
            color: '#2563EB',
            fillColor: '#3B82F6',
            fillOpacity: 0.2,
            radius: initialRadius
        }).addTo(map);

        function updatePosition(lat, lon) {
            latInput.value = lat.toFixed(8);
            lonInput.value = lon.toFixed(8);
            marker.setLatLng([lat, lon]);
            circle.setLatLng([lat, lon]);
        }

        // Marker Drag Event
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updatePosition(position.lat, position.lng);
        });

        // Map Click Event (Click anywhere to place pin)
        map.on('click', function (e) {
            updatePosition(e.latlng.lat, e.latlng.lng);
            map.panTo(e.latlng);
        });

        // Radius Input Change Event
        radiusInput.addEventListener('input', function () {
            const r = parseInt(this.value) || 50;
            circle.setRadius(r);
        });

        // Get Current GPS Location Button
        const btnGPS = document.getElementById('btnGetCurrentLocation');
        btnGPS.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Peramban Anda tidak mendukung akses GPS Geolocation.');
                return;
            }

            btnGPS.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i><span>Mendeteksi Lokasi...</span>';
            lucide.createIcons();

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    updatePosition(lat, lon);
                    map.setView([lat, lon], 17);
                    btnGPS.innerHTML = '<i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i><span>Lokasi Ditemukan!</span>';
                    lucide.createIcons();
                },
                function (err) {
                    alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                    btnGPS.innerHTML = '<i data-lucide="crosshair" class="w-3.5 h-3.5"></i><span>Pusatkan ke Lokasi GPS Saya</span>';
                    lucide.createIcons();
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    });
</script>
@endpush
@endsection
