<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('iron_solder_inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('no')->nullable()->unique(); // nomor urut
            $table->integer('shift')->nullable();
            $table->integer('line')->nullable();
            $table->string('pic');
            $table->string('actual_setting')->nullable();
            $table->string('esd_voltage')->nullable();
            $table->string('eos_ground')->nullable();
            $table->string('solder_tip_condition')->nullable();
            $table->string('solder_stand_condition')->nullable();
            $table->string('judgement')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iron_solder_inspections');
    }
};
