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
        Schema::table('register_f_c_t_s', function (Blueprint $table) {
            $table->string('status_fct')->default('registered')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('register_f_c_t_s', function (Blueprint $table) {
            $table->string('status_fct')->default(null)->change();
        });
    }
};
