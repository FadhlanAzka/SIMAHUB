<?php

namespace Database\Seeders;

use App\Models\OfficeSetting;
use Illuminate\Database\Seeder;

class OfficeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OfficeSetting::updateOrCreate(
            ['id' => 1],
            [
                'office_name' => 'Kantor Instansi MagangHub',
                'latitude' => -6.17539240, // Titik koordinat instansi (dapat diubah di menu Pengaturan)
                'longitude' => 106.82715280,
                'radius_meters' => 100, // Radius toleransi 100 meter
                'work_start_time' => '07:30:00', // Batas waktu keterlambatan
                'work_end_time' => '16:00:00', // Batas waktu mulai absen pulang
            ]
        );
    }
}
