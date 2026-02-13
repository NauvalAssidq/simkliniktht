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
        Schema::create('pemeriksaan_ralan', function (Blueprint $table) {
            $table->string('no_rawat', 17)->primary();
            $table->date('tgl_perawatan');
            $table->time('jam_rawat');
            $table->string('suhu_tubuh', 5)->nullable();
            $table->string('tensi', 8)->nullable();
            $table->string('nadi', 3)->nullable();
            $table->string('respirasi', 3)->nullable();
            $table->string('tinggi', 5)->nullable();
            $table->string('berat', 5)->nullable();
            $table->string('gcs', 10)->nullable();
            $table->text('keluhan')->nullable();
            $table->text('pemeriksaan')->nullable();
            $table->string('alergi', 50)->nullable();
            $table->double('lingkar_perut')->nullable();
            $table->text('rtl')->nullable();
            $table->text('penilaian')->nullable();
            $table->text('instruksi')->nullable();
            $table->text('evaluasi')->nullable();
            $table->string('nip', 20)->nullable();
            $table->timestamps();

            $table->foreign('no_rawat')->references('no_rawat')->on('reg_periksa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_ralan');
    }
};
