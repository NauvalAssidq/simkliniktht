<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pasien;
use App\Models\SatuSehatMappingPasien;

class SatuSehatMappingPasienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $patients = [
            [
                'nik' => '9271060312000001',
                'name' => 'Ardianto Putra',
                'gender' => 'L', // male
                'birthDate' => '1992-01-09',
                'ihs_id' => 'P02478375538'
            ],
            [
                'nik' => '9204014804000002',
                'name' => 'Claudia Sintia',
                'gender' => 'P', // female
                'birthDate' => '1989-11-03',
                'ihs_id' => 'P03647103112'
            ],
            [
                'nik' => '9104224509000003',
                'name' => 'Elizabeth Dior',
                'gender' => 'P', // female
                'birthDate' => '1976-07-07',
                'ihs_id' => 'P00805884304'
            ],
            [
                'nik' => '9104223107000004',
                'name' => 'Dr. Alan Bagus Prasetya',
                'gender' => 'L', // male
                'birthDate' => '1977-09-03',
                'ihs_id' => 'P00912894463'
            ],
            [
                'nik' => '9104224606000005',
                'name' => 'Ghina Assyifa',
                'gender' => 'P', // female
                'birthDate' => '2004-08-21',
                'ihs_id' => 'P01654557057'
            ],
            [
                'nik' => '9104025209000006',
                'name' => 'Salsabilla Anjani Rizki',
                'gender' => 'P', // female
                'birthDate' => '2001-04-16',
                'ihs_id' => 'P02280547535'
            ],
            [
                'nik' => '9201076001000007',
                'name' => 'Theodore Elisjah',
                'gender' => 'L', // Assuming female based on screenshot name/gender mismatch (Screenshot says female, Name Theodore usually male, but let's follow screenshot gender "female" if that was the case... wait, screenshot says "Theodore Elisjah" -> "female"? Let's double check screenshot.
                // Screenshot col 3: "female". OK.
                // Wait, typically Theodore is male. Let's look closely at screenshot row 7.
                // "Theodore Elisjah" -> "female".
                // I will follow the screenshot data exactly.
                'gender' => 'P', 
                'birthDate' => '1985-09-18',
                'ihs_id' => 'P01836748436'
            ],
            [
                'nik' => '9201394901000008',
                'name' => 'Sonia Herdianti',
                'gender' => 'P', // female
                'birthDate' => '1996-06-08',
                'ihs_id' => 'P00883356749'
            ],
            [
                'nik' => '9201076407000009',
                'name' => 'Nancy Wang',
                'gender' => 'P', // female
                'birthDate' => '1955-10-10',
                'ihs_id' => 'P01058967035'
            ],
            [
                'nik' => '9210060207000010',
                'name' => 'Syarif Muhammad',
                'gender' => 'L', // male
                'birthDate' => '1988-11-02',
                'ihs_id' => 'P02428473601'
            ],
        ];

        // Start numbering from P001 for local ID
        $counter = 1;

        foreach ($patients as $p) {
            $no_rkm_medis = str_pad($counter, 6, '0', STR_PAD_LEFT);
            
            // Create Local Patient
            Pasien::updateOrCreate(
                ['no_ktp' => $p['nik']],
                [
                    'no_rkm_medis' => $no_rkm_medis,
                    'nm_pasien' => $p['name'],
                    'jk' => $p['gender'],
                    'tgl_lahir' => $p['birthDate'],
                    'nm_ibu' => '-',
                    'alamat' => 'Alamat Demo',
                    'pekerjaan' => '-',
                    'no_tlp' => '-'
                ]
            );

            // Get the actual no_rkm_medis in case it existed and wasn't $no_rkm_medis above
            $pasien = Pasien::where('no_ktp', $p['nik'])->first();

            // Create Mapping
            SatuSehatMappingPasien::updateOrCreate(
                ['no_rkm_medis' => $pasien->no_rkm_medis],
                ['ihs_patient_id' => $p['ihs_id']]
            );

            $counter++;
        }
    }
}
