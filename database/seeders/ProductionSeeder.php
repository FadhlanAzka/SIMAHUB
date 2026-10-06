<?php

namespace Database\Seeders;

use App\Models\OfficeSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeder data awal untuk server produksi.
 *
 * Aman dijalankan berulang kali: hanya MEMBUAT data yang belum ada,
 * tidak pernah menimpa pengaturan kantor atau mereset password yang sudah ada.
 * Tidak membuat akun intern/mentor dummy maupun data absensi contoh.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('app.seed_admin_password');

        if (blank($password)) {
            throw new RuntimeException('SEED_ADMIN_PASSWORD belum diisi di file .env server.');
        }

        // Pengaturan kantor default, hanya jika belum pernah dibuat
        if (OfficeSetting::query()->doesntExist()) {
            $this->call(OfficeSettingSeeder::class);
        }

        $admins = [
            ['username' => 'admin', 'name' => 'Ita Nurvita, S.E.'],
            ['username' => 'admins', 'name' => 'Super Admin'],
        ];

        foreach ($admins as $admin) {
            User::firstOrCreate(
                ['username' => $admin['username']],
                [
                    'name' => $admin['name'],
                    'role' => 'admin',
                    'password' => $password,
                ]
            );
        }
    }
}
