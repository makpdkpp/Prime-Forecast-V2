<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change onDelete from CASCADE to RESTRICT for user-related FKs
 * in transactional_transfer_history to prevent losing audit history
 * when a user is deleted. transac_id keeps CASCADE (deleting a
 * transaction should delete its transfer history).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactional_transfer_history', function (Blueprint $table) {
            // Drop existing FKs
            $table->dropForeign('transactional_transfer_history_from_user_id_foreign');
            $table->dropForeign('transactional_transfer_history_to_user_id_foreign');
            $table->dropForeign('transactional_transfer_history_transferred_by_foreign');

            // Re-add with RESTRICT
            $table->foreign('from_user_id')->references('user_id')->on('user')->onDelete('restrict');
            $table->foreign('to_user_id')->references('user_id')->on('user')->onDelete('restrict');
            $table->foreign('transferred_by')->references('user_id')->on('user')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('transactional_transfer_history', function (Blueprint $table) {
            $table->dropForeign(['from_user_id']);
            $table->dropForeign(['to_user_id']);
            $table->dropForeign(['transferred_by']);

            $table->foreign('from_user_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('to_user_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('transferred_by')->references('user_id')->on('user')->onDelete('cascade');
        });
    }
};
