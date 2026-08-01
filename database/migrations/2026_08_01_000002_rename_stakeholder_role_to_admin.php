<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Revert the Stakeholder display name so the partner entity is Agency only.
// The 'admin' slug is left untouched so role checks, guards, and policies keep working.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('slug', 'admin')->update(['name' => 'Admin']);
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'admin')->update(['name' => 'Stakeholder']);
    }
};
