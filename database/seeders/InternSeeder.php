<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interns = [
            'Madani Sri Rahayu',
            'Salma Salsabila',
            'Fadhlan Muhammad Azka',
            'Akbar Ramdani',
            'Widia Lestari',
            'Nazwa Aulia Nahdiyin',
            'Triya Haliza Handayani',
            'Febrian Gibran Juliansyah',
            'Sofia Yanifah',
            'Maulida Fitriatusyifa',
            'Alya Siti Aisyah',
            'Firman Fathurohman',
            'Ananda Putri Wandika',
            'Sendhy Pratama',
            'Dede Permana'
        ];

        foreach ($interns as $name) {
            // Ambil kata pertama untuk username, jadikan huruf kecil, dan hapus tanda baca (seperti titik pada Moch.)
            $firstName = strtolower(explode(' ', trim($name))[0]);
            $username = preg_replace('/[^a-z0-9]/', '', $firstName);

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'role' => 'intern',
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
