<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_control_shift1_5h', function (Blueprint $table) {
            $table->id();

            // 🔹 General Info
            $table->date('date');
            $table->string('model');
            $table->string('dj_number');
            $table->foreignId('customer_by')->constrained('customers');
            $table->string('line');
            $table->string('select_shiftgroup');
            $table->string('work_hours')->default('5 Hours');

            // 🔹 Time Slots: store per-slot data as JSON to avoid repeating column names
            // Example structure: [{"slug":"07-08","target":10,"actual_ok":9,...}, ...]
            $table->json('slots')->nullable();

            // 🔹 Totals
            $table->integer('target_total')->nullable();
            $table->integer('actual_total')->nullable();
            $table->integer('ng_total')->nullable();
            $table->integer('balance_total')->nullable();
            $table->integer('loss_total')->nullable();
            $table->string('remarks_total')->nullable();

            // 🔹 Quality Information Sheet
            $table->string('process')->nullable();
            $table->string('ng_item')->nullable();
            $table->string('dj_number_qis')->nullable();
            $table->string('loc')->nullable();
            $table->integer('qty')->nullable();
            $table->string('sop_adr')->nullable();
            $table->string('ipqc')->nullable();

            // 🔹 Operator List
            $table->string('op_process_1')->nullable();
            $table->string('op_name_1')->nullable();
            $table->string('op_process_2')->nullable();
            $table->string('op_name_2')->nullable();

            // 🔹 Output After Change Model
            $table->string('model_output')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('output')->nullable();

            // 🔹 Approval
            $table->string('issued')->default('SOP');
            $table->string('checked')->default('Leader');
            $table->string('approved')->default('SPV');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_control_shift1_5h');
    }
};
