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
        if (! Schema::hasColumn('production_control_shift1s', 'customer_name')) {
            Schema::table('production_control_shift1s', function (Blueprint $table) {
                $table->string('customer_name')->nullable()->after('customer_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('production_control_shift1s', 'customer_name')) {
            Schema::table('production_control_shift1s', function (Blueprint $table) {
                $table->dropColumn('customer_name');
            });
        }
    }
};
