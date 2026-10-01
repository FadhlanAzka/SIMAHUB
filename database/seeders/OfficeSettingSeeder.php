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
                'office_name' => 'Balai Bahasa Provinsi Jawa Barat',
                'latitude' => -6.91388035,
                'longitude' => 107.61710957,
                'radius_meters' => 100,
                'work_start_time' => '07:30:00',
                'work_end_time' => '16:00:00',
                'wfh_days' => [5],
                'work_hours' => [
                    1 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    2 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    3 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    4 => ['start' => '07:30:00', 'end' => '16:00:00'],
                    5 => ['start' => '07:30:00', 'end' => '16:30:00'],
                ],
            ]
        );
    }
}
