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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign(['TicketId'], 'payments_ibfk_1')->references(['TicketId'])->on('tickets')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['CreatedBy'], 'payments_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'payments_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_ibfk_1');
            $table->dropForeign('payments_ibfk_2');
            $table->dropForeign('payments_ibfk_3');
        });
    }
};
