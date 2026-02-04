<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing forecast data from user table to user_forecast_target table
        // Using current year as the fiscal year for existing data
        $currentYear = date('Y');
        
        DB::statement("
            INSERT INTO user_forecast_target (user_id, fiscal_year, target_value, created_at, updated_at)
            SELECT user_id, ?, forecast, NOW(), NOW()
            FROM user
            WHERE forecast > 0
        ", [$currentYear]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated data (optional - be careful with this in production)
        $currentYear = date('Y');
        DB::table('user_forecast_target')
            ->where('fiscal_year', $currentYear)
            ->delete();
    }
};
