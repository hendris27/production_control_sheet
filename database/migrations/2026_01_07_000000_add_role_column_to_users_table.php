<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable `role` column to users and backfills from Spatie roles.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('password');
        });

        // Backfill existing users with their assigned roles (comma-separated)
        if (class_exists(\App\Models\User::class)) {
            \App\Models\User::chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $names = $user->getRoleNames()->toArray();
                    $user->role = is_array($names) ? implode(', ', $names) : ($names ?: null);
                    $user->saveQuietly();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}
