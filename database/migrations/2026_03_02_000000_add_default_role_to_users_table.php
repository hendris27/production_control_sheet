<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set a default value for the `role` column so inserts without a role succeed
        DB::statement("ALTER TABLE `users` MODIFY `role` varchar(255) NOT NULL DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the default while keeping the column NOT NULL (matches original migration)
        DB::statement("ALTER TABLE `users` MODIFY `role` varchar(255) NOT NULL");
    }
};
