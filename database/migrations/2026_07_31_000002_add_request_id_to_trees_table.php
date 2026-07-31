<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            $table->foreignId('request_id')
                ->nullable()
                ->after('id')
                ->constrained('requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('request_id');
        });
    }
};
