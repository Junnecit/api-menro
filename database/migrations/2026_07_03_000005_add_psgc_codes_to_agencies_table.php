<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('region_code', 20)->nullable()->after('phone');
            $table->string('province_code', 20)->nullable()->after('region_code');
            $table->string('municipality_code', 20)->nullable()->after('province_code');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['region_code', 'province_code', 'municipality_code']);
        });
    }
};
