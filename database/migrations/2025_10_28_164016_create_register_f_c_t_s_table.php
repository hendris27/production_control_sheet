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
        Schema::create('register_f_c_t_s', function (Blueprint $table) {
            $table->id();
            $table->date('date_registration');
            $table->string('registration_no')->unique();
            $table->string('customer_by');
            $table->string('fabrication_by');
            $table->string('product_model');
            $table->string('status_fct');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_f_c_t_s');
    }
};
