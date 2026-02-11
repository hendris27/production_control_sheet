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
        if (! Schema::hasColumn('processes', 'nama_proses')) {
            Schema::table('processes', function (Blueprint $table) {
                $table->string('nama_proses')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('processes', 'nama_proses')) {
            Schema::table('processes', function (Blueprint $table) {
                $table->dropColumn('nama_proses');
            });
        }
    }
};
