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
        Schema::create('time_slot_shift2_5hs', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('minutes');
            $table->string('slug')->unique();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
        */
    public function down(): void
    {
        Schema::dropIfExists('time_slot_shift2_5hs');
    }
};
