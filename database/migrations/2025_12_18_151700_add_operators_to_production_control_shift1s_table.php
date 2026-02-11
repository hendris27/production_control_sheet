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
        if (! Schema::hasColumn('production_control_shift1s', 'operators')) {
            Schema::table('production_control_shift1s', function (Blueprint $table) {
                $table->json('operators')->nullable()->after('slots');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('production_control_shift1s', 'operators')) {
            Schema::table('production_control_shift1s', function (Blueprint $table) {
                $table->dropColumn('operators');
            });
        }
    }
};
