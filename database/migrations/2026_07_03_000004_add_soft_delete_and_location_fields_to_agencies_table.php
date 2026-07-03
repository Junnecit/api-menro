<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('barangay_code', 20)->nullable()->after('phone');
            $table->text('custom_address')->nullable()->after('location');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['barangay_code', 'custom_address']);
        });
    }
};
