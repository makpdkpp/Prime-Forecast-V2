<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE transactional SET fiscalyear = fiscalyear - 543 WHERE fiscalyear > 2500");
        if (Schema::hasTable('user_forecast_target')) {
            DB::statement("UPDATE user_forecast_target SET fiscal_year = fiscal_year - 543 WHERE fiscal_year > 2500");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE transactional SET fiscalyear = fiscalyear + 543 WHERE fiscalyear < 2500");
        if (Schema::hasTable('user_forecast_target')) {
            DB::statement("UPDATE user_forecast_target SET fiscal_year = fiscal_year + 543 WHERE fiscal_year < 2500");
        }
    }
};
