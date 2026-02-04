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
        // Add indexes to transactional table
        Schema::table('transactional', function (Blueprint $table) {
            $table->index(['user_id', 'fiscalyear'], 'idx_transactional_user_year');
            $table->index(['team_id', 'fiscalyear'], 'idx_transactional_team_year');
            $table->index('updated_at', 'idx_transactional_updated_at');
            $table->index('contact_start_date', 'idx_transactional_contact_date');
        });

        // Add indexes to transactional_step table
        Schema::table('transactional_step', function (Blueprint $table) {
            $table->index(['transac_id', 'date'], 'idx_transactional_step_transac_date');
            $table->index(['transac_id', 'level_id'], 'idx_transactional_step_transac_level');
        });

        // Add indexes to transactional_transfer_history table
        Schema::table('transactional_transfer_history', function (Blueprint $table) {
            $table->index(['transac_id', 'transferred_at'], 'idx_transfer_history_transac_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from transactional table
        Schema::table('transactional', function (Blueprint $table) {
            $table->dropIndex('idx_transactional_user_year');
            $table->dropIndex('idx_transactional_team_year');
            $table->dropIndex('idx_transactional_updated_at');
            $table->dropIndex('idx_transactional_contact_date');
        });

        // Drop indexes from transactional_step table
        Schema::table('transactional_step', function (Blueprint $table) {
            $table->dropIndex('idx_transactional_step_transac_date');
            $table->dropIndex('idx_transactional_step_transac_level');
        });

        // Drop indexes from transactional_transfer_history table
        Schema::table('transactional_transfer_history', function (Blueprint $table) {
            $table->dropIndex('idx_transfer_history_transac_date');
        });
    }
};
