<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_monitorings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('request_id')
                  ->unique()
                  ->constrained('requests')
                  ->cascadeOnDelete();

            $table->string('seedling_type');
            $table->date('date_monitoring')->nullable();
            $table->unsignedInteger('seedlings_planted')->default(0);
            $table->unsignedInteger('replanted_count')->default(0);
            $table->unsignedInteger('survived_count')->default(0);
            $table->unsignedInteger('died_count')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_monitorings');
    }
};
