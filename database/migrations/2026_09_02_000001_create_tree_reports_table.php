<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tree_reports', function (Blueprint $table) {
            $table->id();

            $table->string('client_uuid')->nullable()->unique();
            $table->string('report_code')->nullable()->unique()->index();

            $table->foreignId('tree_id')
                ->nullable()
                ->constrained('trees')
                ->nullOnDelete();

            $table->foreignId('request_id')
                ->nullable()
                ->constrained('requests')
                ->nullOnDelete();

            $table->foreignId('agency_id')
                ->nullable()
                ->constrained('agencies')
                ->nullOnDelete();

            $table->foreignId('reported_by_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('report_type')->default('damage')->index();
            $table->string('severity')->default('medium')->index();
            $table->string('tree_status_update')->nullable();
            $table->string('status')->default('submitted')->index();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('action_taken')->nullable();

            $table->string('barangay')->nullable()->index();
            $table->string('municipality')->default('Tagoloan');
            $table->string('province')->default('Misamis Oriental');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('landmark')->nullable();

            $table->foreignId('resolved_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['agency_id', 'status']);
            $table->index(['reported_by_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tree_reports');
    }
};
