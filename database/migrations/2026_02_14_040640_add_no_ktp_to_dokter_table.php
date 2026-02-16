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
        Schema::table('dokter', function (Blueprint $table) {
            $table->string('no_ktp', 20)->nullable()->after('nm_dokter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            $table->dropColumn('no_ktp');
        });
    }
};
