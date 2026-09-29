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
        if (!$this->check_in_time || !$this->check_out_time) {
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
     * Badge status keterlambatan
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->status === 'tepat_waktu') {
            return '<div class="inline-flex flex-col items-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Tepat Waktu</span></div>';
        } elseif ($this->status === 'terlambat') {
            $latenessText = '';
            if ($this->check_in_time && $this->date) {
                $office = \App\Models\OfficeSetting::getSetting();
                $dayOfWeek = Carbon::parse($this->date)->dayOfWeekIso;
                
                $workHours = is_string($office->work_hours) ? json_decode($office->work_hours, true) : $office->work_hours;
                $startLimitStr = $workHours[$dayOfWeek]['start'] ?? $office->work_start_time;
                
                $limit = Carbon::parse($startLimitStr);
                $checkIn = Carbon::parse($this->check_in_time);
                
                if ($checkIn->gt($limit)) {
                    $diffMins = $limit->diffInMinutes($checkIn);
                    $hours = (int) floor($diffMins / 60);
                    $mins = $diffMins % 60;
                    
                    if ($hours > 0) {
                        $latenessText = "({$hours}j {$mins}m)";
                    } else {
                        $latenessText = "({$mins} mnt)";
                    }
                    $latenessText = "<span class=\"block text-[9.5px] text-rose-500 font-bold mt-1 leading-none tracking-tight\">{$latenessText}</span>";
                }
            }

            return '<div class="inline-flex flex-col items-center justify-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Terlambat</span>' . $latenessText . '</div>';
        }

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">-</span>';
    }

    /**
     * Badge tipe kehadiran (WFO / WFH)
     */
    public function getTypeBadgeAttribute(): string
    {
        if ($this->type === 'WFH') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 font-semibold">WFH (Bebas Lokasi)</span>';
        }

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 font-semibold">WFO (Kantor)</span>';
    }
}
