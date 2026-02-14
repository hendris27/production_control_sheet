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
            $table->string('customer_name')->nullable();
            $table->string('line')->nullable();
            $table->string('select_shift')->nullable();
            $table->string('select_group')->nullable();
            $table->string('work_hours')->nullable();

            // Production Data - JSON column untuk menyimpan per-slot data
            $table->json('slots')->nullable()->default('[]');
            $table->string('operators')->nullable();
            // Detail NG Record

            $table->integer('qty_ok')->nullable();
            $table->integer('qty_ng')->nullable();
            $table->integer('total_qty')->nullable();

            // Quality Information Sheet
            $table->json('quality_information')->nullable()->default('[]');
            $table->string('process')->nullable();
            $table->string('ng_item')->nullable();
            $table->string('loc')->nullable();
            $table->integer('qty')->nullable()->default(0);
            $table->string('sop_adr')->nullable();
            $table->string('ipqc')->nullable();
            $table->text('remarks_qc')->nullable();

            // Operator List

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
