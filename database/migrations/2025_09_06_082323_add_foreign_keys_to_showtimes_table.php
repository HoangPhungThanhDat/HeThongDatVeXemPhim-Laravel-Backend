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
        Schema::table('showtimes', function (Blueprint $table) {
            $table->foreign(['MovieId'], 'showtimes_ibfk_1')->references(['MovieId'])->on('movies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['RoomId'], 'showtimes_ibfk_2')->references(['RoomId'])->on('rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'showtimes_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'showtimes_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropForeign('showtimes_ibfk_1');
            $table->dropForeign('showtimes_ibfk_2');
            $table->dropForeign('showtimes_ibfk_3');
            $table->dropForeign('showtimes_ibfk_4');
        });
    }
};
