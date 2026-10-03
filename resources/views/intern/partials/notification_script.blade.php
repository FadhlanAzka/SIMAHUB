@php
    $officeSettingForNotif = \App\Models\OfficeSetting::getSetting();
    $dayOfWeekIso = (int) \Carbon\Carbon::now()->dayOfWeekIso;
    $todayHoursNotif = $officeSettingForNotif->getTodayHours($dayOfWeekIso);
    $wfhDaysList = array_values(array_map('intval', is_array($officeSettingForNotif->wfh_days) ? $officeSettingForNotif->wfh_days : (json_decode($officeSettingForNotif->wfh_days, true) ?? [])));
    $isWeekendNotif = ($dayOfWeekIso >= 6);
    $isWfhDayNotif = in_array($dayOfWeekIso, $wfhDaysList, true);
    
    $todayAttNotif = \App\Models\Attendance::where('user_id', auth()->id())
        ->where('date', \Carbon\Carbon::now()->toDateString())
        ->first();
        
    $hasInNotif = $todayAttNotif && $todayAttNotif->check_in_time ? true : false;
    $hasOutNotif = $todayAttNotif && $todayAttNotif->check_out_time ? true : false;
    $checkInTimeFormatted = $todayAttNotif && $todayAttNotif->check_in_time ? substr($todayAttNotif->check_in_time, 0, 5) : '';
    $checkOutTimeFormatted = $todayAttNotif && $todayAttNotif->check_out_time ? substr($todayAttNotif->check_out_time, 0, 5) : '';
    
    $daysMapNotif = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $dayNameNotif = $daysMapNotif[$dayOfWeekIso] ?? 'Hari Ini';
    $workTypeNotif = $isWeekendNotif ? 'LIBUR' : ($isWfhDayNotif ? 'WFH' : 'WFO');
@endphp

<script>
(function() {
    // Konfigurasi Pengingat Presensi Dinamis SIMAHUB
    window.SIMAHUB_NOTIF_CONFIG = {
        userName: @json(auth()->user()->name),
        dayName: @json($dayNameNotif),
        startLimit: @json($todayHoursNotif['start']), // Contoh: 07:30
        endLimit: @json($todayHoursNotif['end']),     // Contoh: 16:00 (Senin-Kamis) atau 16:30 (Jumat)
        hasCheckedIn: {{ $hasInNotif ? 'true' : 'false' }},
        hasCheckedOut: {{ $hasOutNotif ? 'true' : 'false' }},
        checkInTime: @json($checkInTimeFormatted),
        checkOutTime: @json($checkOutTimeFormatted),
        isWeekend: {{ $isWeekendNotif ? 'true' : 'false' }},
        isWfh: {{ $isWfhDayNotif ? 'true' : 'false' }},
        workType: @json($workTypeNotif),
        todayDate: @json(\Carbon\Carbon::now()->toDateString()),
        officeName: @json($officeSettingForNotif->office_name),
    };

    const cfg = window.SIMAHUB_NOTIF_CONFIG;

    // Menghasilkan nada pengingat lonceng lembut (Two-tone Chime) via Web Audio API tanpa perlu file MP3 eksternal
    function playChimeSound() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const now = ctx.currentTime;

            // Nada 1: E5 (659.25 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.2, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.5);

            // Nada 2: A5 (880 Hz) - jeda 120ms
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.12);
            gain2.gain.setValueAtTime(0.25, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.75);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.75);
        } catch (e) {
            console.warn('AudioContext belum dapat diputar otomatis:', e);
        }
    }

    // Mengirim notifikasi browser standar
    function sendBrowserNotification(title, body, tag, icon) {
        if (!('Notification' in window)) {
            console.log('Browser tidak mendukung Web Notification API');
            return null;
        }

        if (Notification.permission === 'granted') {
            playChimeSound();
            try {
                const notif = new Notification(title, {
                    body: body,
                    tag: tag || 'simahub-reminder',
                    renotify: true,
                    requireInteraction: false
                });

                notif.onclick = function() {
                    window.focus();
                    if (!window.location.href.includes('/dashboard')) {
                        window.location.href = "{{ route('dashboard') }}";
                    }
                    this.close();
                };
                return notif;
            } catch (err) {
                console.warn('Gagal memicu desktop notification:', err);
            }
        }
        return null;
    }

    // Ubah string jam 'HH:mm' menjadi menit dari tengah malam
    function timeToMinutes(timeStr) {
        if (!timeStr) return 0;
        const parts = timeStr.split(':');
        return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
    }

    // Sinkronisasi status banner di dashboard secara real-time
    function updateLiveBanner(currentMins, startMins, endMins) {
        const banner = document.getElementById('attendanceReminderBanner');
        if (!banner || cfg.isWeekend) return;

        if (!cfg.hasCheckedIn) {
            if (currentMins >= startMins) {
                // Tampilkan banner peringatan terlambat jika belum absen masuk dan sudah melewati batas waktu
                if (!banner.dataset.state || banner.dataset.state !== 'late') {
                    banner.dataset.state = 'late';
                    banner.innerHTML = `
                        <div class="rounded-2xl bg-rose-50/90 border border-rose-200/90 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                            <div class="flex items-start sm:items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/20">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 animate-pulse"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xs sm:text-sm font-bold text-rose-950">Peringatan: Belum Melakukan Presensi Masuk</h3>
                                        <span class="px-2 py-0.5 rounded-full bg-rose-200 text-rose-900 text-[10px] font-black uppercase tracking-wide">Terlambat</span>
                                    </div>
                                    <p class="text-xs text-rose-800 mt-0.5 leading-relaxed">
                                        Batas waktu masuk pukul <strong class="font-black text-rose-950 font-mono">${cfg.startLimit} WIB</strong> telah lewat. Segera lakukan presensi masuk sekarang agar durasi keterlambatan Anda tidak terus bertambah!
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="#formCheckIn" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="log-in" class="w-4 h-4"></i>
                                    <span>Absen Sekarang</span>
                                </a>
                            </div>
                        </div>`;
                    if (window.lucide) lucide.createIcons();
                }
            }
        } else if (cfg.hasCheckedIn && !cfg.hasCheckedOut) {
            if (currentMins >= endMins) {
                // Tampilkan banner jam pulang jika sudah saatnya absen pulang
                if (!banner.dataset.state || banner.dataset.state !== 'checkout_ready') {
                    banner.dataset.state = 'checkout_ready';
                    banner.innerHTML = `
                        <div class="rounded-2xl bg-purple-50/90 border border-purple-200/90 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                            <div class="flex items-start sm:items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-purple-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-600/20">
                                    <i data-lucide="clock" class="w-5 h-5 animate-pulse"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xs sm:text-sm font-bold text-purple-950">Waktunya Presensi Pulang (Hari ${cfg.dayName})</h3>
                                        <span class="px-2 py-0.5 rounded-full bg-purple-200 text-purple-900 text-[10px] font-black uppercase tracking-wide">Jam Pulang Tiba</span>
                                    </div>
                                    <p class="text-xs text-purple-800 mt-0.5 leading-relaxed">
                                        Jam kerja hari ini telah selesai (pukul <strong class="font-black text-purple-950 font-mono">${cfg.endLimit} WIB</strong>). Jangan lupa lakukan <strong>Absen Pulang</strong> sekarang sebelum meninggalkan instansi atau menutup aktivitas magang Anda!
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="#formCheckOut" class="px-4 py-2 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Absen Pulang Sekarang</span>
                                </a>
                            </div>
                        </div>`;
                    if (window.lucide) lucide.createIcons();
                }
            }
        }
    }

    // Evaluasi jam saat ini dan memicu pengingat jika sudah waktunya
    function checkSchedule() {
        if (cfg.isWeekend) return; // Libur akhir pekan

        const now = new Date();
        const currentMins = (now.getHours() * 60) + now.getMinutes();
        const startMins = timeToMinutes(cfg.startLimit); // Contoh: 07:30 = 450 menit
        const endMins = timeToMinutes(cfg.endLimit);     // Contoh: 16:00 = 960 menit atau 16:30 = 990 menit

        updateLiveBanner(currentMins, startMins, endMins);

        // 1. Pengingat Absen Masuk:
        // Berlaku jika belum absen masuk, mulai dari 15 menit sebelum batas masuk s/d akhir jam operasional
        if (!cfg.hasCheckedIn) {
            const inKey = 'simahub_reminded_in_' + cfg.todayDate;
            // Jika waktu saat ini sudah mencapai atau melewati jam masuk (atau mendekati batas toleransi)
            if (currentMins >= (startMins - 15) && !localStorage.getItem(inKey)) {
                localStorage.setItem(inKey, 'true');
                sendBrowserNotification(
                    '⏰ Waktunya Presensi Masuk! - SIMAHUB',
                    `Halo ${cfg.userName}, batas presensi masuk hari ${cfg.dayName} adalah pukul ${cfg.startLimit} WIB (${cfg.workType}). Segera lakukan absen masuk agar tidak terlambat!`,
                    'simahub-in-' + cfg.todayDate
                );
            }
        }

        // 2. Pengingat Absen Pulang:
        // Berlaku jika sudah absen masuk, tetapi belum absen pulang, tepat saat jam pulang tiba
        if (cfg.hasCheckedIn && !cfg.hasCheckedOut) {
            const outKey = 'simahub_reminded_out_' + cfg.todayDate;
            if (currentMins >= endMins && !localStorage.getItem(outKey)) {
                localStorage.setItem(outKey, 'true');
                sendBrowserNotification(
                    '🔔 Waktunya Presensi Pulang! - SIMAHUB',
                    `Halo ${cfg.userName}, jam kerja hari ${cfg.dayName} telah selesai (pukul ${cfg.endLimit} WIB). Jangan lupa absen pulang sebelum pulang!`,
                    'simahub-out-' + cfg.todayDate
                );
            }
        }
    }

    // Perbarui Tampilan Widget Kontrol Notifikasi di UI
    function updateNotifUI() {
        const badge = document.getElementById('notifStatusBadge');
        const btnText = document.getElementById('btnEnableNotifText');
        const btn = document.getElementById('btnEnableNotif');

        if (!('Notification' in window)) {
            if (badge) {
                badge.className = "text-[10px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-500";
                badge.textContent = "Tidak Didukung";
            }
            if (btn) btn.disabled = true;
            return;
        }

        if (Notification.permission === 'granted') {
            if (badge) {
                badge.className = "text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1";
                badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif';
            }
            if (btnText) btnText.textContent = "Pengingat Aktif";
            if (btn) {
                btn.className = "flex-1 py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold border border-emerald-200 transition-all flex items-center justify-center gap-1.5 cursor-pointer";
            }
        } else if (Notification.permission === 'denied') {
            if (badge) {
                badge.className = "text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-rose-100 text-rose-800";
                badge.textContent = "Diblokir";
            }
            if (btnText) btnText.textContent = "Izin Diblokir";
            if (btn) {
                btn.className = "flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer";
            }
        } else {
            if (badge) {
                badge.className = "text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800";
                badge.textContent = "Belum Aktif";
            }
            if (btnText) btnText.textContent = "Aktifkan Pengingat";
            if (btn) {
                btn.className = "flex-1 py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer";
            }
        }
    }

    // Permintaan Izin Notifikasi Browser
    function requestPermission() {
        if (!('Notification' in window)) {
            Swal.fire({
                icon: 'warning',
                title: 'Peramban Tidak Mendukung',
                text: 'Peramban ini tidak mendukung Web Notification API. Silakan gunakan Google Chrome atau Microsoft Edge.',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        if (Notification.permission === 'granted') {
            playChimeSound();
            Swal.fire({
                icon: 'success',
                title: 'Pengingat Sudah Aktif!',
                html: `Sistem secara otomatis akan mengingatkan Anda setiap:<br><strong>Masuk:</strong> Pukul ${cfg.startLimit} WIB<br><strong>Pulang:</strong> Pukul ${cfg.endLimit} WIB`,
                timer: 3000,
                showConfirmButton: false,
                customClass: { popup: 'rounded-3xl p-6' }
            });
            return;
        }

        Notification.requestPermission().then(function(permission) {
            updateNotifUI();
            if (permission === 'granted') {
                playChimeSound();
                sendBrowserNotification(
                    '🔔 Pengingat Presensi Aktif! - SIMAHUB',
                    `Terima kasih! Anda akan menerima pengingat otomatis pada jam masuk (${cfg.startLimit} WIB) dan pulang (${cfg.endLimit} WIB).`,
                    'simahub-welcome'
                );
                Swal.fire({
                    icon: 'success',
                    title: 'Notifikasi Berhasil Diaktifkan!',
                    text: `Pengingat presensi otomatis aktif pada jam ${cfg.startLimit} dan ${cfg.endLimit} WIB.`,
                    timer: 3000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl p-6' }
                });
            } else if (permission === 'denied') {
                Swal.fire({
                    icon: 'error',
                    title: 'Izin Notifikasi Ditolak',
                    text: 'Untuk mengaktifkan pengingat, klik ikon gembok di sebelah kiri bilah URL peramban Anda dan izinkan Notifikasi.',
                    confirmButtonColor: '#2563eb'
                });
            }
        });
    }

    // Uji Coba Pengingat (Dapat dicoba langsung oleh mentor atau mahasiswa)
    function testNotification() {
        playChimeSound();
        const testTitle = '🔔 Uji Notifikasi Presensi - SIMAHUB';
        const testBody = `Uji notifikasi berhasil! Sistem siap mengingatkan absen Masuk (${cfg.startLimit} WIB) dan Pulang (${cfg.endLimit} WIB).`;

        if ('Notification' in window && Notification.permission === 'granted') {
            sendBrowserNotification(testTitle, testBody, 'simahub-test-' + Date.now());
        }

        // Tampilkan juga SweetAlert toast agar selalu terlihat di layar
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Uji Pengingat Presensi Berhasil!',
            text: `Jadwal Pengingat: Masuk (${cfg.startLimit} WIB) | Pulang (${cfg.endLimit} WIB)`,
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-blue-100 bg-white'
            }
        });
    }

    // Ekspor API Global untuk komponen UI
    window.SIMAHUB_NOTIF = {
        config: cfg,
        playChime: playChimeSound,
        sendNotification: sendBrowserNotification,
        requestPermission: requestPermission,
        testNotification: testNotification,
        checkSchedule: checkSchedule,
        updateUI: updateNotifUI
    };

    // Jalankan scheduler saat DOM siap
    document.addEventListener('DOMContentLoaded', function() {
        updateNotifUI();
        checkSchedule();
        
        // Cek interval setiap 15 detik untuk memantau jam masuk & pulang secara presisi
        setInterval(checkSchedule, 15000);

        // Bind event listeners ke tombol widget jika elemen tersedia
        const btnEnable = document.getElementById('btnEnableNotif');
        if (btnEnable) {
            btnEnable.addEventListener('click', requestPermission);
        }

        const btnTest = document.getElementById('btnTestNotif');
        if (btnTest) {
            btnTest.addEventListener('click', testNotification);
        }
    });
})();
</script>
