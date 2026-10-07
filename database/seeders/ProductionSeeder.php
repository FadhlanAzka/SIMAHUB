<?php

namespace Database\Seeders;

use App\Models\OfficeSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan kantor default, hanya jika belum pernah dibuat
        if (OfficeSetting::query()->doesntExist()) {
            $this->call(OfficeSettingSeeder::class);
        }

        $seedPassword = config('app.seed_admin_password');
        if (empty($seedPassword)) {
            throw new \RuntimeException('SEED_ADMIN_PASSWORD is not set in environment.');
        }

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Ita Nurvita, S.E.',
                'role' => 'admin',
                'password' => Hash::make($seedPassword),
            ]
        );

        User::firstOrCreate(
            ['username' => 'mentor1'],
            [
                'name' => 'Akhmad Susanto, S.E.',
                'role' => 'mentor',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'mentor2'],
            [
                'name' => 'Lailatul Munawaroh, S.Pd, M.Pd.',
                'role' => 'mentor',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'mentor3'],
            [
                'name' => 'Evi Fuji Fauziyah, M.Hum.',
                'role' => 'mentor',
                'password' => Hash::make('password'),
            ]
        );

        $interns = [
            ['name' => 'Madani Sri Rahayu', 'username' => 'madani', 'phone' => '089501817401', 'position' => 'Pranata Humas', 'mentor_id' => 2],
            ['name' => 'Fadhlan Muhammad Azka', 'username' => 'padlan', 'phone' => '082216699635', 'position' => 'Pengolah Data dan Informasi (Informatika)', 'mentor_id' => 2],
            ['name' => 'Moch. Akbar Ramdani', 'username' => 'akbar', 'phone' => '083877429651', 'position' => 'Pengolah Data dan Informasi (Informatika)', 'mentor_id' => 2],
            ['name' => 'Salma Salsabila', 'username' => 'salma', 'phone' => '089523875080', 'position' => 'Pengolah Data dan Informasi (Keuangan)', 'mentor_id' => 2],
            ['name' => 'Widia Lestari', 'username' => 'widia', 'phone' => '085523500319', 'position' => 'Pustakawan', 'mentor_id' => 2],
            ['name' => 'Nazwa Aulia Nahdiyin', 'username' => 'nazwa', 'phone' => '089518956743', 'position' => 'Pustakawan', 'mentor_id' => 2],
            ['name' => 'Febrian Gibran Juliansyah', 'username' => 'febrian', 'phone' => '081563401712', 'position' => 'Pustakawan', 'mentor_id' => 2],
            ['name' => 'Triya Haliza Handayani', 'username' => 'triya', 'phone' => '089648090179', 'position' => 'Pustakawan', 'mentor_id' => 2],
            ['name' => 'Sofia Yanifah', 'username' => 'sofia', 'phone' => '081214976705', 'position' => 'Penelaah Teknis Kebijakan (Bahasa Indonesia)', 'mentor_id' => 3],
            ['name' => 'Maulida Fitriatusyifa', 'username' => 'maulida', 'phone' => '087821991399', 'position' => 'Penelaah Teknis Kebijakan (Bahasa Indonesia)', 'mentor_id' => 3],
            ['name' => 'Firman Fathurohman', 'username' => 'firman', 'phone' => '089505721919', 'position' => 'Penelaah Teknis Kebijakan (Bahasa Sunda)', 'mentor_id' => 4],
            ['name' => 'Alya Siti Aisyah', 'username' => 'alya', 'phone' => '081222744348', 'position' => 'Penelaah Teknis Kebijakan (Bahasa Sunda)', 'mentor_id' => 4],
            ['name' => 'Dede Permana', 'username' => 'dede', 'phone' => '081395482374', 'position' => 'Penelaah Teknis Kebijakan (Bahasa Sunda)', 'mentor_id' => 4],
            ['name' => 'Sendhy Pratama', 'username' => 'sendhy', 'phone' => '083838352743', 'position' => 'Pengolah Data dan Informasi (Desain, Videografi, Editing)', 'mentor_id' => 2],
            ['name' => 'Ananda Putri Wandika', 'username' => 'ananda', 'phone' => '081223409004', 'position' => 'Pengolah Data dan Informasi (Desain, Videografi, Editing)', 'mentor_id' => 2],
        ];

        foreach ($interns as $intern) {
            User::firstOrCreate(
                ['username' => $intern['username']],
                [
                    'name' => $intern['name'],
                    'role' => 'intern',
                    'position' => $intern['position'],
                    'phone' => $intern['phone'],
                    'mentor_id' => $intern['mentor_id'],
                    'password' => Hash::make('password'),
                ]
            );
        }

        User::firstOrCreate(
            ['username' => 'admins'],
            [
                'name' => 'Super Admin',
                'role' => 'admin',
                'password' => Hash::make($seedPassword),
            ]
        );
    }
}
