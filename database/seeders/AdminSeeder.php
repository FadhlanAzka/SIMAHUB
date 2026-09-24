<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya membuat 1 akun Admin resmi (bukan nama dosen / pihak luar)
        User::updateOrCreate(
            ['email' => 'admin@simahub.id'],
            [
                'name' => 'Admin',
                'nim' => 'ADMIN',
                'institution' => 'Instansi MagangHub',
                'position' => 'Administrator Instansi',
                'phone' => '081234567890',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}
