<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('soil_document_path')->nullable()->after('soil_type');
            $table->string('soil_document_name')->nullable()->after('soil_document_path');
            $table->string('soil_document_mime')->nullable()->after('soil_document_name');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn([
                'soil_document_path',
                'soil_document_name',
                'soil_document_mime',
            ]);
        });
    }
};
