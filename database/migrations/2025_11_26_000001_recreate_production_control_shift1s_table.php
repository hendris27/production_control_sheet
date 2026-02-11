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
        // Drop tabel lama jika ada
        Schema::dropIfExists('production_control_shift1s');

        // Buat tabel baru
        Schema::create('production_control_shift1s', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('model')->nullable();
            $table->string('dj_number')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('line')->nullable();
            $table->string('select_shiftgroup')->nullable()->default('1/A');
            $table->string('work_hours')->nullable()->default('7 Hours');
            $table->string('shift_group')->nullable()->default('1/A');
            
            // Production Data - JSON column untuk menyimpan per-slot data
            $table->json('slots')->nullable()->default('[]');
            
            // Quality Information Sheet
            $table->string('process')->nullable();
            $table->string('ng_item')->nullable();
            $table->string('loc')->nullable();
            $table->integer('qty')->nullable()->default(0);
            $table->string('sop_adr')->nullable();
            $table->string('ipqc')->nullable();
            $table->text('remarks_qc')->nullable();
            
            // Operator List
            $table->string('op_process_1')->nullable();
            $table->string('op_name_1')->nullable();
            $table->string('op_process_2')->nullable();
            $table->string('op_name_2')->nullable();
            
            // Output After Change Model
            $table->string('model_output')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('output')->nullable()->default(0);
            
            // Totals (untuk display/calculation)
            $table->integer('target_total')->nullable()->default(0);
            $table->integer('actual_total')->nullable()->default(0);
            $table->integer('ng_total')->nullable()->default(0);
            $table->integer('loss_total')->nullable()->default(0);
            $table->integer('balance_total')->nullable()->default(0);
            
            // Approval
            $table->string('issued')->nullable()->default('SOP');
            $table->string('checked')->nullable()->default('Leader');
            $table->string('approved')->nullable()->default('SPV');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_control_shift1s');
    }
};
