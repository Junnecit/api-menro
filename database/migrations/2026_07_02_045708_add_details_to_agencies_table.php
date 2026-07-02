<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->enum('type', [
                'Government Agency',
                'Local Government',
                'Private Individual',
                'Cooperative',
                'NGO',
            ])->nullable()->after('name');
            $table->string('contact')->nullable()->after('type');
            $table->string('email')->nullable()->after('contact');
            $table->string('phone')->nullable()->after('email');
            $table->string('location')->nullable()->after('phone');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['type', 'contact', 'email', 'phone', 'location', 'status']);
        });
    }
};
