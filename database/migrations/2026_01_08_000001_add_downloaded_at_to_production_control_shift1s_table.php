<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            $table->timestamp('downloaded_at')->nullable()->after('balance_total');
        });
    }

    public function down()
    {
        Schema::table('production_control_shift1s', function (Blueprint $table) {
            $table->dropColumn('downloaded_at');
        });
    }
};
