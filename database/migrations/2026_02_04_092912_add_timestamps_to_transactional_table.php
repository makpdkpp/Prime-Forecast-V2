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
        Schema::table('transactional', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('transac_id');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
        
        // Set default timestamps for existing records
        DB::statement('UPDATE transactional SET created_at = NOW(), updated_at = NOW() WHERE created_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactional', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
