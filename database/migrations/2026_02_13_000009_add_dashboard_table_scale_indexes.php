<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactional', function (Blueprint $table) {
            if (!$this->hasIndex('transactional', 'idx_transactional_year_contact_date')) {
                $table->index(['fiscalyear', 'contact_start_date'], 'idx_transactional_year_contact_date');
            }
            if (!$this->hasIndex('transactional', 'idx_transactional_team_contact_date')) {
                $table->index(['team_id', 'contact_start_date'], 'idx_transactional_team_contact_date');
            }
            if (!$this->hasIndex('transactional', 'idx_transactional_updated_transac')) {
                $table->index(['updated_at', 'transac_id'], 'idx_transactional_updated_transac');
            }
        });

        Schema::table('transactional_step', function (Blueprint $table) {
            if (!$this->hasIndex('transactional_step', 'idx_transactional_step_transac_stepid')) {
                $table->index(['transac_id', 'transacstep_id'], 'idx_transactional_step_transac_stepid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactional', function (Blueprint $table) {
            if ($this->hasIndex('transactional', 'idx_transactional_year_contact_date')) {
                $table->dropIndex('idx_transactional_year_contact_date');
            }
            if ($this->hasIndex('transactional', 'idx_transactional_team_contact_date')) {
                $table->dropIndex('idx_transactional_team_contact_date');
            }
            if ($this->hasIndex('transactional', 'idx_transactional_updated_transac')) {
                $table->dropIndex('idx_transactional_updated_transac');
            }
        });

        Schema::table('transactional_step', function (Blueprint $table) {
            if ($this->hasIndex('transactional_step', 'idx_transactional_step_transac_stepid')) {
                $table->dropIndex('idx_transactional_step_transac_stepid');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        return (bool) $exists;
    }
};
