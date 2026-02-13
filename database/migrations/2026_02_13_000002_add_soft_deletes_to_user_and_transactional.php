<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add soft deletes to user and transactional tables
 * to prevent permanent data loss on deletion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->softDeletes()->after('token_expiry');
        });

        Schema::table('transactional', function (Blueprint $table) {
            $table->softDeletes()->after('timestamp');
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('transactional', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
