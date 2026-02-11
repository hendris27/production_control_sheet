<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            // Hapus kolom customer lama
            $table->dropColumn('customer');
            
            // Tambah kolom customer_id baru
            $table->foreignId('customer_id')
                ->after('line')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            // Hapus kolom customer_id
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            
            // Kembalikan kolom customer
            $table->string('customer')->after('line');
        });
    }
};