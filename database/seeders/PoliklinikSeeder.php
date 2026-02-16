<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliklinikSeeder extends Seeder
{
    public function run()
    {
        DB::table('poliklinik')->insertOrIgnore([
            [
                'kd_poli' => 'U0001',
                'nm_poli' => 'Poli THT',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        DB::table('satu_sehat_mapping_lokasi_ralan')->insertOrIgnore([
            [
                'kd_poli' => 'U0001',
                'ihs_location_id' => 'b017aa54-f1df-460f-9dc0-68153b564fcf',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
