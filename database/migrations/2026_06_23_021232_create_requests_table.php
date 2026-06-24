<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();

            $table->string('request_no')->unique();

            $table->foreignId('agency_id')
                  ->nullable()
                  ->constrained('agencies')
                  ->onDelete('cascade');

            $table->string('requester_name')->nullable();

            $table->string('location');

            $table->enum('status', [
                'Pending',
                'Approved',
                'Completed',
                'Rejected',
                'In Progress'
            ])->default('Pending');

            $table->date('request_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
