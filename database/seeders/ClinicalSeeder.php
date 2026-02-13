<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('penyakit')->insertOrIgnore([
            ['kd_penyakit' => 'H60.9', 'nm_penyakit' => 'Otitis externa, unspecified', 'ciri_ciri' => '-', 'keterangan' => '-', 'kd_ktg' => '-', 'status' => 'Tidak Menular', 'created_at' => now(), 'updated_at' => now()],
            ['kd_penyakit' => 'H61.2', 'nm_penyakit' => 'Impacted cerumen', 'ciri_ciri' => '-', 'keterangan' => '-', 'kd_ktg' => '-', 'status' => 'Tidak Menular', 'created_at' => now(), 'updated_at' => now()],
            ['kd_penyakit' => 'J00', 'nm_penyakit' => 'Acute nasopharyngitis [common cold]', 'ciri_ciri' => '-', 'keterangan' => '-', 'kd_ktg' => '-', 'status' => 'Menular', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('jns_perawatan')->insertOrIgnore([
            [
                'kd_jenis_prw' => 'THT001', 
                'nm_perawatan' => 'Irigasi Telinga', 
                'kd_kategori' => '-', 
                'material' => 10000, 
                'bhp' => 5000, 
                'tarif_tindakandr' => 50000, 
                'tarif_tindakanpr' => 0, 
                'kso' => 0, 
                'menejemen' => 0, 
                'total_byr' => 65000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'kd_jenis_prw' => 'THT002', 
                'nm_perawatan' => 'Ekstraksi Serumen', 
                'kd_kategori' => '-', 
                'material' => 5000, 
                'bhp' => 5000, 
                'tarif_tindakandr' => 40000, 
                'tarif_tindakanpr' => 0, 
                'kso' => 0, 
                'menejemen' => 0, 
                'total_byr' => 50000, 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);
        
        $this->command->info('Clinical Data Seeded (ICD-10 & Procedures)');
    }
}
