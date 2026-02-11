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
        if (Schema::hasTable('production_control_shift1s')) {
            Schema::table('production_control_shift1s', function (Blueprint $table) {
                if (Schema::hasColumn('production_control_shift1s', 'op_process_1')) {
                    $table->dropColumn('op_process_1');
                }
                if (Schema::hasColumn('production_control_shift1s', 'op_name_1')) {
                    $table->dropColumn('op_name_1');
                }
                if (Schema::hasColumn('production_control_shift1s', 'op_process_2')) {
                    $table->dropColumn('op_process_2');
                }
                if (Schema::hasColumn('production_control_shift1s', 'op_name_2')) {
                    $table->dropColumn('op_name_2');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('production_control_shift1s')) {
            Schema::table('production_control_shift1s', function (Blueprint $table) {
                if (! Schema::hasColumn('production_control_shift1s', 'op_process_1')) {
                    $table->string('op_process_1')->nullable()->after('issued');
                }
                if (! Schema::hasColumn('production_control_shift1s', 'op_name_1')) {
                    $table->string('op_name_1')->nullable()->after('op_process_1');
                }
                if (! Schema::hasColumn('production_control_shift1s', 'op_process_2')) {
                    $table->string('op_process_2')->nullable()->after('op_name_1');
                }
                if (! Schema::hasColumn('production_control_shift1s', 'op_name_2')) {
                    $table->string('op_name_2')->nullable()->after('op_process_2');
                }
            });
        }
    }
};
