<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tree_id')
                  ->constrained('trees')
                  ->onDelete('cascade');

            $table->string('path');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tree_photos');
    }
};
