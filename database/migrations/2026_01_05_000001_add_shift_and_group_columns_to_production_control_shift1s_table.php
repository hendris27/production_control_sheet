<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('production_control_shift1s')) {
            return;
        }

        Schema::table('production_control_shift1s', function (Blueprint $table) {
            if (! Schema::hasColumn('production_control_shift1s', 'select_shift')) {
                $table->string('select_shift')->nullable()->after('line')->default('1');
            }
            if (! Schema::hasColumn('production_control_shift1s', 'select_group')) {
                $table->string('select_group')->nullable()->after('select_shift')->default('A');
            }
            if (! Schema::hasColumn('production_control_shift1s', 'shift')) {
                $table->string('shift')->nullable()->after('select_group')->default('1');
            }
            if (! Schema::hasColumn('production_control_shift1s', 'group')) {
                $table->string('group')->nullable()->after('shift')->default('A');
            }
        });

        // Populate new fields from existing `select_shiftgroup` values (format: "1/A")
        DB::table('production_control_shift1s')->whereNotNull('select_shiftgroup')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $shift = null;
                $group = null;
                if (! empty($row->select_shiftgroup) && strpos($row->select_shiftgroup, '/') !== false) {
                    [$shift, $group] = explode('/', $row->select_shiftgroup, 2);
                } elseif (! empty($row->select_shiftgroup)) {
                    // fallback: try to parse first char as shift and remainder as group
                    $shift = substr($row->select_shiftgroup, 0, 1);
                    $group = substr($row->select_shiftgroup, 2) ?: null;
                }

                DB::table('production_control_shift1s')->where('id', $row->id)->update([
                    'select_shift' => $shift ?? '1',
                    'select_group' => $group ?? 'A',
                    'shift' => $shift ?? '1',
                    'group' => $group ?? 'A',
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('production_control_shift1s')) {
            return;
        }

        Schema::table('production_control_shift1s', function (Blueprint $table) {
            if (Schema::hasColumn('production_control_shift1s', 'select_shift')) {
                $table->dropColumn('select_shift');
            }
            if (Schema::hasColumn('production_control_shift1s', 'select_group')) {
                $table->dropColumn('select_group');
            }
            if (Schema::hasColumn('production_control_shift1s', 'shift')) {
                $table->dropColumn('shift');
            }
            if (Schema::hasColumn('production_control_shift1s', 'group')) {
                $table->dropColumn('group');
            }
        });
    }
};
