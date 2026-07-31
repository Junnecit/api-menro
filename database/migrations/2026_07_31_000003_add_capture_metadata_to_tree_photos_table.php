<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tree_photos', function (Blueprint $table) {
            $table->string('capture_mode', 16)->nullable()->after('path');
            $table->string('angle', 1)->nullable()->after('capture_mode');
        });
    }

    public function down(): void
    {
        Schema::table('tree_photos', function (Blueprint $table) {
            $table->dropColumn(['capture_mode', 'angle']);
        });
    }
};
