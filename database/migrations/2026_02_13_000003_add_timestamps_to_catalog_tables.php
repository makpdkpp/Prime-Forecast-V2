<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add created_at and updated_at timestamps to all catalog/lookup tables
 * for better audit trail tracking.
 */
return new class extends Migration
{
    private array $tables = [
        'team_catalog',
        'company_catalog',
        'role_catalog',
        'product_group',
        'priority_level',
        'position',
        'source_of_the_budget',
        'step',
        'industry_group',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'created_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->timestamps();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropTimestamps();
                });
            }
        }
    }
};
