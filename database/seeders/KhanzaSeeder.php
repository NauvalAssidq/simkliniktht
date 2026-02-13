<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhanzaSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Dokter
        DB::table('dokter')->insert([
            ['kd_dokter' => 'D001', 'nm_dokter' => 'dr. THT Satu', 'jk' => 'L', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['kd_dokter' => 'D002', 'nm_dokter' => 'dr. THT Dua', 'jk' => 'P', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Sample Poliklinik
        DB::table('poliklinik')->insert([
            ['kd_poli' => 'U0001', 'nm_poli' => 'Poli THT', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['kd_poli' => 'U0002', 'nm_poli' => 'Poli Umum', 'status' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Sample Mapping (Optional for now)
    }
}
