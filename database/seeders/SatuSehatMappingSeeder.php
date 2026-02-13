<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuSehatMappingSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping Dokter
        // PENTING: Ganti 'YOUR_REAL_PRACTITIONER_ID' dengan ID Praktisi asli dari SatuSehat
        DB::table('satu_sehat_mapping_dokter')->insertOrIgnore([
            ['kd_dokter' => 'D001', 'ihs_practitioner_id' => 'YOUR_REAL_PRACTITIONER_ID', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Mapping Lokasi
        // PENTING: Ganti 'YOUR_REAL_LOCATION_ID' dengan ID Lokasi asli dari SatuSehat
        DB::table('satu_sehat_mapping_lokasi_ralan')->insertOrIgnore([
            ['kd_poli' => 'U0001', 'ihs_location_id' => 'YOUR_REAL_LOCATION_ID', 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        $this->command->info('Please update database/seeders/SatuSehatMappingSeeder.php with your REAL SatuSehat IDs and run: php artisan db:seed --class=SatuSehatMappingSeeder');
    }
}
