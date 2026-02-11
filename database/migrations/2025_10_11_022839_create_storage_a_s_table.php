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
        Schema::create('storage_a_s', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();
            $table->string('category')->nullable();
            $table->string('model_name')->nullable();
            $table->string('location')->nullable();
            $table->string('customer')->nullable();
            $table->string('status')->nullable();
            $table->string('remark')->nullable();
            $table->string('pic')->nullable();
            $table->string('shift')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_a_s');
    }
};
