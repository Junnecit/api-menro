<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tree_report_photos');
    }

    public function down(): void
    {
        Schema::create('tree_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_report_id')->constrained('tree_reports')->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }
};
