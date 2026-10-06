<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dates = [
            '2026-09-21',
            '2026-09-22',
            '2026-09-23',
            '2026-09-24',
            '2026-09-25',
            '2026-09-28',
            '2026-09-29',
            '2026-09-30',
        ];

        $interns = User::where('role', 'intern')->get();

        foreach ($dates as $date) {
            foreach ($interns as $intern) {
                // Tentukan WFH/WFO (Misal Jumat WFH, sisanya WFO)
                // Cek hari dari $date
                $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
                $type = ($dayOfWeek == 5) ? 'WFH' : 'WFO'; // Jumat WFH, sisanya WFO

                Attendance::updateOrCreate(
                    [
                        'user_id' => $intern->id,
                        'date' => $date,
                    ],
                    [
                        'check_in_time' => '07:15:00', // Tepat waktu
                        'check_out_time' => '16:05:00', // Sesuai jadwal pulang
                        'status' => 'tepat_waktu',
                        'type' => $type,
                        'check_in_lat' => '-6.914744',
                        'check_in_long' => '107.609810',
                        'check_out_lat' => '-6.914744',
                        'check_out_long' => '107.609810',
                        'check_in_distance' => 15,
                        'check_out_distance' => 15,
                        'notes' => 'Hadir sesuai jadwal (Seeder)',
                    ]
                );
            }
        }
    }
}
