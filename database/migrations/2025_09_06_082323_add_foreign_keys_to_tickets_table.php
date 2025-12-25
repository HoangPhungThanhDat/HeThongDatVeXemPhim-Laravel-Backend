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
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign(['ShowtimeId'], 'tickets_ibfk_1')->references(['ShowtimeId'])->on('showtimes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['SeatId'], 'tickets_ibfk_2')->references(['SeatId'])->on('seats')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['UserId'], 'tickets_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['CreatedBy'], 'tickets_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'tickets_ibfk_5')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_ibfk_1');
            $table->dropForeign('tickets_ibfk_2');
            $table->dropForeign('tickets_ibfk_3');
            $table->dropForeign('tickets_ibfk_4');
            $table->dropForeign('tickets_ibfk_5');
        });
    }
};
