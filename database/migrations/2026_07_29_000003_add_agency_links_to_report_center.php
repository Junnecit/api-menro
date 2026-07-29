<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_folders', function (Blueprint $table) {
            $table->foreignId('agency_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('agencies')
                ->nullOnDelete();
        });

        Schema::table('report_files', function (Blueprint $table) {
            $table->string('source_key', 100)->nullable()->after('source');
            $table->index(['folder_id', 'source_key']);
        });
    }

    public function down(): void
    {
        Schema::table('report_files', function (Blueprint $table) {
            $table->dropIndex(['folder_id', 'source_key']);
            $table->dropColumn('source_key');
        });

        Schema::table('report_folders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agency_id');
        });
    }
};
