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
        Schema::create('satu_sehat_mapping_pasien', function (Blueprint $table) {
            $table->string('no_rkm_medis', 15)->primary();
            $table->string('ihs_patient_id', 36)->nullable();
            $table->timestamps();

            $table->foreign('no_rkm_medis')->references('no_rkm_medis')->on('pasien')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satu_sehat_mapping_pasien');
    }
};
