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
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign(['UserId'], 'notifications_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'notifications_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'notifications_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_ibfk_1');
            $table->dropForeign('notifications_ibfk_2');
            $table->dropForeign('notifications_ibfk_3');
        });
    }
};
