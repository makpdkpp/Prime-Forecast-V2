<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('is_active')->comment('เปิดใช้ 2FA หรือไม่');
            $table->string('two_factor_code', 255)->nullable()->after('two_factor_enabled')->comment('OTP code (hashed)');
            $table->datetime('two_factor_expires_at')->nullable()->after('two_factor_code')->comment('เวลาหมดอายุของ OTP');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_code', 'two_factor_expires_at']);
        });
    }
};
