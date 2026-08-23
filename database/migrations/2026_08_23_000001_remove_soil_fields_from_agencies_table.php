<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('agencies', 'soil_type')) {
                $columnsToDrop[] = 'soil_type';
            }
            if (Schema::hasColumn('agencies', 'soil_document_path')) {
                $columnsToDrop[] = 'soil_document_path';
            }
            if (Schema::hasColumn('agencies', 'soil_document_name')) {
                $columnsToDrop[] = 'soil_document_name';
            }
            if (Schema::hasColumn('agencies', 'soil_document_mime')) {
                $columnsToDrop[] = 'soil_document_mime';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('soil_type')->nullable();
            $table->string('soil_document_path')->nullable();
            $table->string('soil_document_name')->nullable();
            $table->string('soil_document_mime')->nullable();
        });
    }
};
