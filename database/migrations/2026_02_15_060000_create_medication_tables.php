<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drug Master (KFA)
        Schema::create('databarang', function (Blueprint $table) {
            $table->string('kd_brng', 30)->primary();
            $table->string('nm_brng', 250);
            $table->string('satuan', 30)->nullable();           // e.g. tablet, botol, kapsul
            $table->string('kode_form', 15)->nullable();         // medication-form code e.g. BS086
            $table->string('nm_form', 100)->nullable();          // e.g. Tetes Telinga
            $table->double('harga')->default(0);
            $table->integer('stok')->default(0);
            $table->timestamps();
        });

        // 2. Prescription Header
        Schema::create('resep_obat', function (Blueprint $table) {
            $table->string('no_resep', 20)->primary();
            $table->string('no_rawat', 17);
            $table->string('kd_dokter', 20);
            $table->date('tgl_resep');
            $table->time('jam_resep');
            $table->enum('status', ['menunggu', 'diberikan', 'batal'])->default('menunggu');
            $table->timestamps();

            $table->foreign('no_rawat')->references('no_rawat')->on('reg_periksa')->onDelete('cascade');
            $table->foreign('kd_dokter')->references('kd_dokter')->on('dokter')->onDelete('cascade');
        });

        // 3. Prescription Items
        Schema::create('detail_resep_obat', function (Blueprint $table) {
            $table->id();
            $table->string('no_resep', 20);
            $table->string('kd_brng', 30)->nullable();          // nullable for free-text drugs
            $table->string('nm_obat_manual', 250)->nullable();   // fallback name if not in master
            $table->double('jumlah')->default(1);
            $table->string('dosis', 100)->nullable();            // e.g. 500mg
            $table->string('frekuensi', 100)->nullable();        // e.g. 3x sehari
            $table->string('instruksi', 250)->nullable();        // e.g. setelah makan
            $table->timestamps();

            $table->foreign('no_resep')->references('no_resep')->on('resep_obat')->onDelete('cascade');
            $table->foreign('kd_brng')->references('kd_brng')->on('databarang')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_resep_obat');
        Schema::dropIfExists('resep_obat');
        Schema::dropIfExists('databarang');
    }
};
