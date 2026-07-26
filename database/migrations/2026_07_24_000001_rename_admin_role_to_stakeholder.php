<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Self-registered web accounts use the 'admin' slug (full admin interface +
// powers) but are now presented as "Stakeholder". The slug is deliberately
// left untouched so every role check, route guard, and policy keeps working.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('slug', 'admin')->update(['name' => 'Stakeholder']);
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'admin')->update(['name' => 'Admin']);
    }
};
