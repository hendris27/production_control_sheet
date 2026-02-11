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
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            // Tambah default value untuk dj_number jika belum ada
            if (!Schema::hasColumn('production_control_shift1s', 'dj_number')) {
                $table->string('dj_number')->nullable()->after('model');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            //
        });
    }
};
