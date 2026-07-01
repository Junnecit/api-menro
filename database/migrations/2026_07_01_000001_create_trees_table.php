<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trees', function (Blueprint $table) {
            $table->id();

            $table->string('tree_code')->nullable()->unique();
            $table->string('species');
            $table->string('common_name')->nullable();

            $table->enum('status', [
                'alive',
                'dead',
                'need_replacement',
            ])->default('alive');

            $table->date('date_planted')->nullable();
            $table->date('date_recorded');

            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable()->default('Tagoloan');
            $table->string('province')->nullable()->default('Misamis Oriental');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('landmark')->nullable();

            $table->foreignId('inspector_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->foreignId('recorded_by_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->foreignId('agency_id')
                  ->nullable()
                  ->constrained('agencies')
                  ->onDelete('set null');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trees');
    }
};
