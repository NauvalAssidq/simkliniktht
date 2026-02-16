<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin System (IT / Owner)
        User::updateOrCreate(
            ['email' => 'admin@simklinik.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Receptionist (Pendaftaran)
        User::updateOrCreate(
            ['email' => 'receptionist@simklinik.com'],
            [
                'name' => 'Staff Pendaftaran',
                'password' => Hash::make('password'),
                'role' => 'pendaftaran',
            ]
        );


        
        // 4. Apothecary (Apotek / Farmasi)
         User::updateOrCreate(
            ['email' => 'apothecary@simklinik.com'],
            [
                'name' => 'Staff Farmasi',
                'password' => Hash::make('password'),
                'role' => 'apotek',
            ]
        );
    }
}
