<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('avatar_path', 255)->nullable()->default(null)->change();
            $table->string('reset_token', 64)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('avatar_path', 255)->nullable(false)->change();
            $table->string('reset_token', 64)->nullable(false)->change();
        });
    }
};
