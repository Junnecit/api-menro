<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add targeted indexes to speed up the dashboard stats query.
     *
     * requests:
     *   - (status, deleted_at) — covers the three-in-one COUNT(*) GROUP BY status
     *   - (user_id, deleted_at) — speeds up the ownedBy whereIn scope
     *
     * trees:
     *   - (request_id) — already created by the foreignId constraint, no-op here;
     *     kept for documentation. The key win is on requests.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Composite index on the columns most filtered in every dashboard query.
            $table->index(['status', 'deleted_at'], 'requests_status_deleted_at_idx');
            $table->index(['user_id', 'deleted_at'], 'requests_user_id_deleted_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex('requests_status_deleted_at_idx');
            $table->dropIndex('requests_user_id_deleted_at_idx');
        });
    }
};
