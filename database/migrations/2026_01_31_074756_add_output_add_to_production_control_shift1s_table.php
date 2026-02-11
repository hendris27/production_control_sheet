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
            $table->time('start_time_add')->nullable()->after('output');
            $table->time('end_time_add')->nullable()->after('start_time_add');
            $table->integer('output_add')->nullable()->after('end_time_add');
            $table->integer('output_total_ok')->nullable()->after('output_add');
            $table->timestamp('downloaded_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            $table->dropColumn([
                'start_time_add',
                'end_time_add',
                'output_add',
                'output_total_ok',
                'downloaded_at',
            ]);
        });
    }
};
