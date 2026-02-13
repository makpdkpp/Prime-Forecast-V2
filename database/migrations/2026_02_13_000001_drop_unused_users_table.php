<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the unused 'users' table (Laravel default).
 * The system uses the 'user' table (singular) instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('users');
    }

    public function down(): void
    {
        // Not recreating — this table was never used by the application.
    }
};
