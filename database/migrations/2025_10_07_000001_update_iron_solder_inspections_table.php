<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('iron_solder_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('iron_solder_inspections', 'shift')) {
                $table->integer('shift')->nullable()->default(null);
            } else {
                $table->integer('shift')->nullable()->default(null)->change();
            }
            if (!Schema::hasColumn('iron_solder_inspections', 'line')) {
                $table->integer('line')->nullable();
            } else {
                $table->integer('line')->change();
            }
            if (!Schema::hasColumn('iron_solder_inspections', 'tanggal')) {
                $table->date('tanggal')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('iron_solder_inspections', function (Blueprint $table) {
            $table->string('shift')->change();
            $table->string('line')->change();
            $table->dropColumn('tanggal');
        });
    }
};
