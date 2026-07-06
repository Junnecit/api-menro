<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The admin who manages this user. Each user belongs to one admin;
            // an admin manages many users and owns their records. Null for
            // admins/super-admins and unassigned users.
            $table->foreignId('admin_id')->nullable()->after('role_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });
    }
};
