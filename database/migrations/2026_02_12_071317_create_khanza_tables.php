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
        Schema::create('pasien', function (Blueprint $table) {
            $table->string('no_rkm_medis', 15)->primary();
            $table->string('nm_pasien', 40)->nullable();
            $table->string('no_ktp', 20)->nullable();
            $table->enum('jk', ['L', 'P'])->nullable();
            $table->string('tmp_lahir', 15)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('nm_ibu', 40)->nullable(); 
            $table->string('alamat', 200)->nullable();
            $table->string('no_tlp', 40)->nullable();
            $table->string('pekerjaan', 60)->nullable();
            $table->timestamps();
        });

        Schema::create('dokter', function (Blueprint $table) {
            $table->string('kd_dokter', 20)->primary();
            $table->string('nm_dokter', 50)->nullable();
            $table->enum('jk', ['L', 'P'])->nullable();
            $table->string('tmp_lahir', 20)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('no_ijn_praktek', 120)->nullable();
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamps();
        });

        Schema::create('poliklinik', function (Blueprint $table) {
            $table->string('kd_poli', 5)->primary();
            $table->string('nm_poli', 50)->nullable();
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamps();
        });

        Schema::create('reg_periksa', function (Blueprint $table) {
            $table->string('no_reg', 8);
            $table->string('no_rawat', 17)->primary();
            $table->date('tgl_registrasi')->nullable();
            $table->time('jam_reg')->nullable();
            $table->string('kd_dokter', 20)->nullable();
            $table->string('no_rkm_medis', 15)->nullable();
            $table->string('kd_poli', 5)->nullable();
            $table->string('p_jawab', 100)->nullable();
            $table->string('almt_pj', 200)->nullable();
            $table->string('hubunganpj', 20)->nullable();
            $table->double('biaya_reg')->nullable();
            $table->enum('stts', ['Belum', 'Sudah', 'Batal', 'Berkas Diterima', 'Dirujuk', 'Meninggal', 'Dirawat', 'Pulang Paksa', 'Status'])->default('Belum');
            $table->enum('stts_daftar', ['Lama', 'Baru'])->nullable();
            $table->string('status_lanjut', 5)->nullable();
            $table->string('kd_pj', 3)->nullable();
            $table->integer('umurdaftar')->nullable();
            $table->enum('sttsumur', ['Th', 'Bl', 'Hr'])->nullable();
            $table->enum('status_bayar', ['Sudah Bayar', 'Belum Bayar'])->nullable();
            
            $table->foreign('kd_dokter')->references('kd_dokter')->on('dokter')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('no_rkm_medis')->references('no_rkm_medis')->on('pasien')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('kd_poli')->references('kd_poli')->on('poliklinik')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('antripoli', function (Blueprint $table) {
            $table->string('kd_poli', 5);
            $table->string('kd_dokter', 20);
            $table->enum('no_antrian', ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']);
            $table->integer('angka_antrian');
            $table->string('no_rawat', 17)->nullable();
            $table->date('tgl_antrian')->nullable();
            $table->enum('status', ['0', '1'])->default('0');
            
            $table->index(['kd_poli', 'kd_dokter']);
            $table->foreign('kd_poli')->references('kd_poli')->on('poliklinik')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('kd_dokter')->references('kd_dokter')->on('dokter')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('satu_sehat_encounter', function (Blueprint $table) {
            $table->string('no_rawat', 17)->primary();
            $table->string('id_encounter', 36)->nullable();
            $table->dateTime('waktu_kirim')->nullable();
            $table->enum('status', ['terkirim', 'gagal'])->default('terkirim');
            $table->text('response')->nullable();
            
            $table->foreign('no_rawat')->references('no_rawat')->on('reg_periksa')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('satu_sehat_mapping_lokasi_ralan', function (Blueprint $table) {
            $table->string('kd_poli', 5)->primary();
            $table->string('ihs_location_id', 36)->nullable();
            $table->timestamps();
        });

        Schema::create('satu_sehat_mapping_dokter', function (Blueprint $table) {
            $table->string('kd_dokter', 20)->primary();
            $table->string('ihs_practitioner_id', 36)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satu_sehat_mapping_dokter');
        Schema::dropIfExists('satu_sehat_mapping_lokasi_ralan');
        Schema::dropIfExists('satu_sehat_encounter');
        Schema::dropIfExists('antripoli');
        Schema::dropIfExists('reg_periksa');
        Schema::dropIfExists('poliklinik');
        Schema::dropIfExists('dokter');
        Schema::dropIfExists('pasien');
    }
};
