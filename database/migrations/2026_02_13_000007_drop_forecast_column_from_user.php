<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the legacy 'forecast' column from the user table.
 * Forecast data has been migrated to user_forecast_target table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('forecast');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->integer('forecast')->default(0)->after('position_id');
        });
    }
};
