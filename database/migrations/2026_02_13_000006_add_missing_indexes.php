<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing indexes to improve query performance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->index('status', 'idx_company_requests_status');
        });

        Schema::table('transactional', function (Blueprint $table) {
            $table->index('Source_budget_id', 'idx_transactional_source_budget');
            $table->index('Step_id', 'idx_transactional_step_id');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->index('position_id', 'idx_user_position');
        });
    }

    public function down(): void
    {
        Schema::table('company_requests', function (Blueprint $table) {
            $table->dropIndex('idx_company_requests_status');
        });

        Schema::table('transactional', function (Blueprint $table) {
            $table->dropIndex('idx_transactional_source_budget');
            $table->dropIndex('idx_transactional_step_id');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropIndex('idx_user_position');
        });
    }
};
