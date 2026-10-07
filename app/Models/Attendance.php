<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'type',
        'check_in_lat',
        'check_in_long',
        'check_in_distance',
        'check_out_lat',
        'check_out_long',
        'check_out_distance',
        'notes',
        'attachment',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung durasi jam kerja jika sudah check out
     */
    public function getDurationAttribute(): ?string
    {
        if (in_array($this->status, ['izin', 'sakit', 'alpa'])) {
            return ucfirst($this->status);
        }

        if (! $this->check_in_time || ! $this->check_out_time) {
            return '-';
        }

        $in = Carbon::parse($this->check_in_time);
        $out = Carbon::parse($this->check_out_time);

        // Bulatkan ke bawah dan kurangi 1 jam (istirahat)
        $hours = (int) floor($in->diffInHours($out));
        $hours = max(0, $hours - 1); // Pastikan tidak kurang dari 0

        $minutes = $in->diffInMinutes($out) % 60;

        return "{$hours} jam {$minutes} mnt";
    }

    /**
     * Hitung selisih menit keterlambatan jika status terlambat
     */
    public function getLateMinutesAttribute(): int
    {
        if ($this->status !== 'terlambat' || ! $this->check_in_time || ! $this->date) {
            return 0;
        }

        $office = OfficeSetting::getSetting();
        $dayOfWeek = Carbon::parse($this->date)->dayOfWeekIso;

        $workHours = is_string($office->work_hours) ? json_decode($office->work_hours, true) : $office->work_hours;
        $startLimitStr = $workHours[$dayOfWeek]['start'] ?? $office->work_start_time;

        $limit = Carbon::parse($startLimitStr);
        $checkIn = Carbon::parse($this->check_in_time);

        if ($checkIn->gt($limit)) {
            return (int) $limit->diffInMinutes($checkIn);
        }

        return 0;
    }

    /**
     * Format durasi keterlambatan (contoh: 0 jam 30 Menit)
     */
    public function getLateDurationTextAttribute(): ?string
    {
        $minutes = $this->late_minutes;
        if ($minutes <= 0) {
            return null;
        }

        $hours = (int) floor($minutes / 60);
        $mins = $minutes % 60;

        return "{$hours} jam {$mins} Menit";
    }

    /**
     * Format status teks untuk ekspor spreadsheet / cetak
     */
    public function getStatusWithLateDurationAttribute(): string
    {
        if ($this->status === 'tepat_waktu') {
            return 'Tepat Waktu';
        } elseif ($this->status === 'terlambat') {
            $duration = $this->late_duration_text ?: '0 jam 0 Menit';

            return "TELAT\n({$duration})";
        } elseif ($this->status === 'izin') {
            return 'Izin';
        } elseif ($this->status === 'sakit') {
            return 'Sakit';
        } elseif ($this->status === 'dispensasi') {
            return 'Dispensasi (Lupa Absen)';
        } elseif ($this->status === 'alpa') {
            return 'Alpa';
        }

        return '-';
    }

    /**
     * Badge status keterlambatan
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'tepat_waktu') {
            return '<div class="inline-flex flex-col items-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Tepat Waktu</span></div>';
        } elseif ($this->status === 'terlambat') {
            $duration = $this->late_duration_text ?: '0 jam 0 Menit';

            return '<div class="inline-flex flex-col items-center justify-center leading-tight">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-800 tracking-wide">TELAT</span>
                <span class="text-[10px] text-rose-600 font-bold mt-0.5">('.$duration.')</span>
            </div>';
        } elseif ($this->status === 'izin') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Izin</span>';
        } elseif ($this->status === 'sakit') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Sakit</span>';
        } elseif ($this->status === 'dispensasi') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800" title="Dispensasi Lupa Absen oleh Admin">Dispensasi</span>';
        } elseif ($this->status === 'alpa') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">Alpa</span>';
        }

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">-</span>';
    }

    /**
     * Badge tipe kehadiran (WFO / WFH)
     */
    public function getTypeBadgeAttribute(): string
    {
        if ($this->status === 'izin') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Perizinan</span>';
        } elseif ($this->status === 'sakit') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Surat Sakit</span>';
        } elseif ($this->status === 'alpa') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700">Tidak Hadir</span>';
        }

        if ($this->type === 'WFH') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 font-semibold">WFH (Bebas Lokasi)</span>';
        }

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 font-semibold">WFO (Kantor)</span>';
    }

    /**
     * URL file lampiran PDF surat sakit / izin
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment) {
            return null;
        }

        return route('attendance.attachment', $this->id);
    }
}
