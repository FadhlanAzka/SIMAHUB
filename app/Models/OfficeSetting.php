<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_name',
        'latitude',
        'longitude',
        'radius_meters',
        'work_start_time',
        'work_end_time',
        'wfh_days',
        'work_hours',
    ];

    protected $casts = [
        'wfh_days' => 'array',
        'work_hours' => 'array',
    ];

    /**
     * Mengambil konfigurasi kantor utama (singleton-like).
     */
    public static function getSetting(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'office_name' => 'Kantor Instansi MagangHub',
                'latitude' => -6.17539240,
                'longitude' => 106.82715280,
                'radius_meters' => 100,
                'work_start_time' => '07:30:00',
                'work_end_time' => '16:00:00',
                'wfh_days' => [5], // Default Jumat WFH
                'work_hours' => [
                    1 => ['start' => '07:30', 'end' => '16:00'],
                    2 => ['start' => '07:30', 'end' => '16:00'],
                    3 => ['start' => '07:30', 'end' => '16:00'],
                    4 => ['start' => '07:30', 'end' => '16:00'],
                    5 => ['start' => '07:30', 'end' => '16:30'],
                ],
            ]
        );
    }

    /**
     * Mengambil array nama hari WFH yang aktif
     */
    public function getWfhDayNamesAttribute(): array
    {
        $daysMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
        $wfh = is_array($this->wfh_days) ? $this->wfh_days : (json_decode($this->wfh_days, true) ?? []);
        $names = [];
        foreach ($wfh as $d) {
            $intD = (int) $d;
            if (isset($daysMap[$intD])) {
                $names[] = $daysMap[$intD];
            }
        }
        return $names;
    }

    /**
     * Format teks fleksibel hari WFH (Contoh: "Senin", "Senin, Selasa", atau "Tidak ada (Full WFO di kantor)")
     */
    public function getWfhDaysTextAttribute(): string
    {
        $names = $this->wfh_day_names;
        if (empty($names)) {
            return 'Tidak ada (Full WFO di kantor)';
        }
        return implode(', ', $names);
    }

    /**
     * Mengambil konfigurasi jam masuk dan pulang spesifik per hari
     */
    public function getTodayHours(?int $dayOfWeek = null): array
    {
        $dayOfWeek = $dayOfWeek ?? (int) \Carbon\Carbon::now()->dayOfWeekIso;
        $wh = is_array($this->work_hours) ? $this->work_hours : (json_decode($this->work_hours, true) ?? []);

        $start = $wh[$dayOfWeek]['start'] ?? $wh[(string)$dayOfWeek]['start'] ?? substr($this->work_start_time ?? '07:30', 0, 5);
        $end = $wh[$dayOfWeek]['end'] ?? $wh[(string)$dayOfWeek]['end'] ?? substr($this->work_end_time ?? ($dayOfWeek == 5 ? '16:30' : '16:00'), 0, 5);

        return [
            'start' => substr($start, 0, 5),
            'end' => substr($end, 0, 5),
        ];
    }
}
