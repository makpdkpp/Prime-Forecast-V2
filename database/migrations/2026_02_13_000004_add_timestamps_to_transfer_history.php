<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add created_at and updated_at to transactional_transfer_history
 * for complete audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactional_transfer_history', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('transactional_transfer_history', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
