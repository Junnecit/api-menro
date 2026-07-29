<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('report_folders')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'name']);
        });

        Schema::create('report_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('report_folders')
                ->nullOnDelete();
            $table->string('name');
            $table->string('path');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('source', 50)->default('upload');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['folder_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_files');
        Schema::dropIfExists('report_folders');
    }
};
