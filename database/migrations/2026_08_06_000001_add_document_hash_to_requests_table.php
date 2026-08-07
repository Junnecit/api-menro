<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('document_hash', 64)->nullable()->after('document_mime');
            $table->index('document_hash');
            $table->index('project_name');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex(['document_hash']);
            $table->dropIndex(['project_name']);
            $table->dropColumn('document_hash');
        });
    }
};
