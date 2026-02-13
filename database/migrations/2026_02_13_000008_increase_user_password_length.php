<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Increase user.password from varchar(50) to varchar(255)
 * to support bcrypt (60 chars) and Argon2 (95+ chars) hashes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('password', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('password', 50)->change();
        });
    }
};
