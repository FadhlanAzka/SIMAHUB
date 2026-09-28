<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['username' => 'pengawas'],
            [
                'name' => 'Pengawas',
                'role' => 'pengawas',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Akun Mentor
        User::updateOrCreate(
            ['username' => 'mentor'],
            [
                'name' => 'Mentor',
                'role' => 'mentor',
                'password' => Hash::make('password'),
            ]
        );

        // 4. Data intern (Berdasarkan Database Lama)
        $interns = [
            ['name' => 'Madani Sri Rahayu', 'username' => 'madani', 'phone' => '089501817401', 'position' => null],
            ['name' => 'Salma Salsabila', 'username' => 'salma', 'phone' => '089523875080', 'position' => null],
            ['name' => 'Fadhlan Muhammad Azka', 'username' => 'padlan', 'phone' => '082216699635', 'position' => 'Pengolah Data dan Informasi (Informatika)'],
            ['name' => 'Moch. Akbar Ramdani', 'username' => 'akbar', 'phone' => '083877429651', 'position' => 'Pengolah Data dan Informasi (Informatika)'],
            ['name' => 'Widia Lestari', 'username' => 'widia', 'phone' => '085523500319', 'position' => null],
            ['name' => 'Nazwa Aulia Nahdiyin', 'username' => 'nazwa', 'phone' => '089518956743', 'position' => null],
            ['name' => 'Triya Haliza Handayani', 'username' => 'triya', 'phone' => '089648090179', 'position' => null],
            ['name' => 'Febrian Gibran Juliansyah', 'username' => 'febrian', 'phone' => '081563401712', 'position' => null],
            ['name' => 'Sofia Yanifah', 'username' => 'sofia', 'phone' => '081214976705', 'position' => null],
            ['name' => 'Maulida Fitriatusyifa', 'username' => 'maulida', 'phone' => '087821991399', 'position' => null],
            ['name' => 'Alya Siti Aisyah', 'username' => 'alya', 'phone' => '081222744348', 'position' => null],
            ['name' => 'Firman Fathurohman', 'username' => 'firman', 'phone' => '089505721919', 'position' => null],
            ['name' => 'Ananda Putri Wandika', 'username' => 'ananda', 'phone' => '081223409004', 'position' => null],
            ['name' => 'Sendhy Pratama', 'username' => 'sendhy', 'phone' => '083838352743', 'position' => null],
            ['name' => 'Dede Permana', 'username' => 'dede', 'phone' => '081395482374', 'position' => null],
        ];

        foreach ($interns as $intern) {
            User::updateOrCreate(
                ['username' => $intern['username']],
                [
                    'name' => $intern['name'],
                    'role' => 'intern',
                    'position' => $intern['position'],
                    'phone' => $intern['phone'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
