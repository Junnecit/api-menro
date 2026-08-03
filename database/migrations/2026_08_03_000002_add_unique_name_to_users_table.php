<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make existing duplicate display names unique before adding the index.
        $duplicates = DB::table('users')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicates as $name) {
            $users = DB::table('users')
                ->where('name', $name)
                ->orderBy('id')
                ->get(['id', 'name']);

            foreach ($users as $index => $user) {
                if ($index === 0) {
                    continue;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['name' => $user->name.' ('.$user->id.')']);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
