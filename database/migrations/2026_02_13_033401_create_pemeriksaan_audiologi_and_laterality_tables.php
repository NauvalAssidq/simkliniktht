<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemeriksaan_audiologi', function (Blueprint $table) {
            $table->string('no_rawat', 20)->primary();
            
            // Telinga Kanan
            $table->string('tipe_gangguan_kanan')->nullable(); // Conductive, Sensorineural, Mixed, None
            $table->double('ambang_dengar_kanan')->nullable(); // dB
            
            // Telinga Kiri
            $table->string('tipe_gangguan_kiri')->nullable();
            $table->double('ambang_dengar_kiri')->nullable();

            $table->timestamps();
        });

        Schema::create('pemeriksaan_ralan_laterality', function (Blueprint $table) {
            $table->id();
            $table->string('no_rawat', 20)->index();
            $table->string('kode_brng', 20); // Stores kd_penyakit or kd_jenis_prw
            $table->enum('jenis', ['Diagnosis', 'Procedure']);
            $table->enum('sisi', ['Kanan', 'Kiri', 'Kedua']);
            $table->timestamps();

            $table->index(['no_rawat', 'kode_brng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_ralan_laterality');
        Schema::dropIfExists('pemeriksaan_audiologi');
    }
};
