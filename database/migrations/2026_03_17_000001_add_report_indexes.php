<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactional', function (Blueprint $table) {
            if (!$this->hasIndex('transactional', 'idx_transactional_bidding_date')) {
                $table->index('date_of_closing_of_sale', 'idx_transactional_bidding_date');
            }
            if (!$this->hasIndex('transactional', 'idx_transactional_contract_date')) {
                $table->index('sales_can_be_close', 'idx_transactional_contract_date');
            }
            if (!$this->hasIndex('transactional', 'idx_transactional_user_bidding')) {
                $table->index(['user_id', 'date_of_closing_of_sale'], 'idx_transactional_user_bidding');
            }
            if (!$this->hasIndex('transactional', 'idx_transactional_user_contract')) {
                $table->index(['user_id', 'sales_can_be_close'], 'idx_transactional_user_contract');
            }
        });

        Schema::table('transactional_step', function (Blueprint $table) {
            if (!$this->hasIndex('transactional_step', 'idx_transactional_step_level_date')) {
                $table->index(['level_id', 'date'], 'idx_transactional_step_level_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactional', function (Blueprint $table) {
            if ($this->hasIndex('transactional', 'idx_transactional_bidding_date')) {
                $table->dropIndex('idx_transactional_bidding_date');
            }
            if ($this->hasIndex('transactional', 'idx_transactional_contract_date')) {
                $table->dropIndex('idx_transactional_contract_date');
            }
            if ($this->hasIndex('transactional', 'idx_transactional_user_bidding')) {
                $table->dropIndex('idx_transactional_user_bidding');
            }
            if ($this->hasIndex('transactional', 'idx_transactional_user_contract')) {
                $table->dropIndex('idx_transactional_user_contract');
            }
        });

        Schema::table('transactional_step', function (Blueprint $table) {
            if ($this->hasIndex('transactional_step', 'idx_transactional_step_level_date')) {
                $table->dropIndex('idx_transactional_step_level_date');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        return (bool) $exists;
    }
};
