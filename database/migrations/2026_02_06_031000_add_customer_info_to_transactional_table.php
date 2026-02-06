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
        Schema::table('transactional', function (Blueprint $table) {
            $table->string('contact_person', 255)->nullable()->after('remark')->comment('ชื่อผู้ติดต่อ');
            $table->string('contact_phone', 50)->nullable()->after('contact_person')->comment('เบอร์โทรศัพท์');
            $table->string('contact_email', 255)->nullable()->after('contact_phone')->comment('อีเมล');
            $table->text('contact_note')->nullable()->after('contact_email')->comment('หมายเหตุข้อมูลลูกค้า');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactional', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_phone', 'contact_email', 'contact_note']);
        });
    }
};
