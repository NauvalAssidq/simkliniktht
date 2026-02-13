<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Dokter;
use App\Models\SatuSehatMappingDokter;

class SatuSehatMappingDokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Data from user screenshot
        $doctors = [
            [
                'kd_dokter' => 'DR001',
                'nm_dokter' => 'dr. Alexander',
                'jk' => 'L',
                'tmp_lahir' => 'Jakarta',
                'tgl_lahir' => '1994-01-01',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 1',
                'no_telp' => '081234567890',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => '-',
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10009880728'
            ],
            [
                'kd_dokter' => 'DR002',
                'nm_dokter' => 'dr. Yoga Yandika, Sp.A',
                'jk' => 'L',
                'tmp_lahir' => 'Bandung',
                'tgl_lahir' => '1995-02-02',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 2',
                'no_telp' => '081234567891',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0001', // Spesialis Anak
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10006926841'
            ],
            [
                'kd_dokter' => 'DR003',
                'nm_dokter' => 'dr. Syarifuddin, Sp.PD',
                'jk' => 'L',
                'tmp_lahir' => 'Surabaya',
                'tgl_lahir' => '1988-03-03',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 3',
                'no_telp' => '081234567892',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0002', // Spesialis Penyakit Dalam
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10001354453'
            ],
            [
                'kd_dokter' => 'DR004',
                'nm_dokter' => 'dr. Nicholas Evan, Sp.B',
                'jk' => 'L',
                'tmp_lahir' => 'Medan',
                'tgl_lahir' => '1986-04-04',
                'gol_drh' => '-',
                'agama' => 'KRISTEN',
                'almt_tgl' => 'Jl. Test No. 4',
                'no_telp' => '081234567893',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0003', // Spesialis Bedah
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10010910332'
            ],
            [
                'kd_dokter' => 'DR005',
                'nm_dokter' => 'dr. Dito Arifin, Sp.M',
                'jk' => 'L',
                'tmp_lahir' => 'Yogyakarta',
                'tgl_lahir' => '1985-05-05',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 5',
                'no_telp' => '081234567894',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0004', // Spesialis Mata
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10018180913'
            ],
            [
                'kd_dokter' => 'DR006',
                'nm_dokter' => 'dr. Olivia Kirana, Sp.OG',
                'jk' => 'P',
                'tmp_lahir' => 'Semarang',
                'tgl_lahir' => '1984-06-06',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 6',
                'no_telp' => '081234567895',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0005', // Spesialis Kandungan
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10002074224'
            ],
            [
                'kd_dokter' => 'DR007',
                'nm_dokter' => 'dr. Alicia Chrissy, Sp.N',
                'jk' => 'P',
                'tmp_lahir' => 'Denpasar',
                'tgl_lahir' => '1982-07-07',
                'gol_drh' => '-',
                'agama' => 'HINDU',
                'almt_tgl' => 'Jl. Test No. 7',
                'no_telp' => '081234567896',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0006', // Spesialis Saraf
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10012572188'
            ],
            [
                'kd_dokter' => 'DR008',
                'nm_dokter' => 'dr. Nathalie Tan, Sp.PK',
                'jk' => 'P',
                'tmp_lahir' => 'Makassar',
                'tgl_lahir' => '1981-08-08',
                'gol_drh' => '-',
                'agama' => 'KRISTEN',
                'almt_tgl' => 'Jl. Test No. 8',
                'no_telp' => '081234567897',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => 'S0007', // Spesialis Patologi Klinik
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10018452434'
            ],
            // Sheila Annisa S.Kep (Perawat typically, assigning generic code)
            [
                'kd_dokter' => 'PR001',
                'nm_dokter' => 'Sheila Annisa S.Kep',
                'jk' => 'P',
                'tmp_lahir' => 'Jakarta',
                'tgl_lahir' => '1980-09-09',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 9',
                'no_telp' => '081234567898',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => '-',
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10014058550'
            ],
            // apt. Aditya Pradhana, S.Farm (Apoteker typically)
            [
                'kd_dokter' => 'AP001',
                'nm_dokter' => 'apt. Aditya Pradhana, S.Farm',
                'jk' => 'P', // Screenshot says female, S.Farm typically male name but adhering to screenshot (female)
                'tmp_lahir' => 'Jakarta',
                'tgl_lahir' => '1980-10-10',
                'gol_drh' => '-',
                'agama' => 'ISLAM',
                'almt_tgl' => 'Jl. Test No. 10',
                'no_telp' => '081234567899',
                'stts_nikah' => 'MENIKAH',
                'kd_sps' => '-',
                'alumni' => '-',
                'no_ijn_praktek' => '-',
                'status' => '1',
                'ihs_id' => '10001915884'
            ]
        ];

        foreach ($doctors as $doc) {
            // Create or update Dokter
            Dokter::updateOrCreate(
                ['kd_dokter' => $doc['kd_dokter']],
                [
                    'nm_dokter' => $doc['nm_dokter'],
                    'jk' => $doc['jk'],
                    'tmp_lahir' => $doc['tmp_lahir'],
                    'tgl_lahir' => $doc['tgl_lahir'],
                    'no_ijn_praktek' => $doc['no_ijn_praktek'],
                    'status' => $doc['status']
                ]
            );

            // Create or update Mapping
            SatuSehatMappingDokter::updateOrCreate(
                ['kd_dokter' => $doc['kd_dokter']],
                ['ihs_practitioner_id' => $doc['ihs_id']]
            );
        }
    }
}
