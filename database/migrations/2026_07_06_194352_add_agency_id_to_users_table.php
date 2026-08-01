<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The agency this admin account represents. Each
            // agency has at most one admin (enforced via unique). Null for
            // non-admin roles.
            $table->foreignId('agency_id')->nullable()->unique()->after('admin_id')
                ->constrained('agencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agency_id');
        });
    }
};
