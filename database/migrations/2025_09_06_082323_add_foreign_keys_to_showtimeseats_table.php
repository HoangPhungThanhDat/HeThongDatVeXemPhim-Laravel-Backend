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
        Schema::table('showtimeseats', function (Blueprint $table) {
            $table->foreign(['ShowtimeId'], 'showtimeseats_ibfk_1')->references(['ShowtimeId'])->on('showtimes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['SeatId'], 'showtimeseats_ibfk_2')->references(['SeatId'])->on('seats')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'showtimeseats_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'showtimeseats_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimeseats', function (Blueprint $table) {
            $table->dropForeign('showtimeseats_ibfk_1');
            $table->dropForeign('showtimeseats_ibfk_2');
            $table->dropForeign('showtimeseats_ibfk_3');
            $table->dropForeign('showtimeseats_ibfk_4');
        });
    }
};
