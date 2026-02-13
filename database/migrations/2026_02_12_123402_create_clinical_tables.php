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
        // 1. Penyakit (ICD-10)
        Schema::create('penyakit', function (Blueprint $table) {
            $table->string('kd_penyakit', 15)->primary();
            $table->string('nm_penyakit', 250)->nullable();
            $table->text('ciri_ciri')->nullable();
            $table->string('keterangan', 60)->nullable();
            $table->string('kd_ktg', 8)->nullable();
            $table->enum('status', ['Menular', 'Tidak Menular'])->nullable();
            $table->timestamps();
        });

        // 2. Diagnosa Pasien
        Schema::create('diagnosa_pasien', function (Blueprint $table) {
            $table->string('no_rawat', 17);
            $table->string('kd_penyakit', 15);
            $table->enum('status', ['Ralan', 'Ranap'])->default('Ralan');
            $table->tinyInteger('prioritas')->default(1);
            $table->enum('status_penyakit', ['Lama', 'Baru'])->default('Baru');
            $table->timestamps();

            $table->primary(['no_rawat', 'kd_penyakit', 'prioritas']); // Composite Key
            $table->foreign('no_rawat')->references('no_rawat')->on('reg_periksa')->onDelete('cascade');
            $table->foreign('kd_penyakit')->references('kd_penyakit')->on('penyakit')->onDelete('cascade');
        });

        // 3. Jenis Perawatan (Procedures Master)
        Schema::create('jns_perawatan', function (Blueprint $table) {
            $table->string('kd_jenis_prw', 15)->primary();
            $table->string('nm_perawatan', 80)->nullable();
            $table->string('kd_kategori', 5)->nullable();
            $table->double('material')->default(0);
            $table->double('bhp')->default(0);
            $table->double('tarif_tindakandr')->default(0);
            $table->double('tarif_tindakanpr')->default(0);
            $table->double('kso')->default(0);
            $table->double('menejemen')->default(0);
            $table->double('total_byr')->default(0);
            $table->timestamps();
        });

        // 4. Rawat Jalan Dokter (Procedures Transaction)
        Schema::create('rawat_jl_dr', function (Blueprint $table) {
            $table->string('no_rawat', 17);
            $table->string('kd_jenis_prw', 15);
            $table->string('kd_dokter', 20);
            $table->date('tgl_perawatan');
            $table->time('jam_rawat');
            $table->double('material')->default(0);
            $table->double('bhp')->default(0);
            $table->double('tarif_tindakandr')->default(0);
            $table->double('kso')->default(0);
            $table->double('menejemen')->default(0);
            $table->double('biaya_rawat')->default(0);
            $table->timestamps();

            $table->foreign('no_rawat')->references('no_rawat')->on('reg_periksa')->onDelete('cascade');
            $table->foreign('kd_jenis_prw')->references('kd_jenis_prw')->on('jns_perawatan')->onDelete('cascade');
            $table->foreign('kd_dokter')->references('kd_dokter')->on('dokter')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rawat_jl_dr');
        Schema::dropIfExists('jns_perawatan');
        Schema::dropIfExists('diagnosa_pasien');
        Schema::dropIfExists('penyakit');
    }
};
