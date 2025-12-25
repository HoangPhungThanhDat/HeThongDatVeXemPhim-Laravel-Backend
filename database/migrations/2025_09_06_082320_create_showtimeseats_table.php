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
        Schema::create('showtimeseats', function (Blueprint $table) {
            $table->integer('ShowtimeSeatId', true);
            $table->integer('ShowtimeId')->index('idx_showtimeseat_showtime');
            $table->integer('SeatId')->index('seatid');
            $table->enum('Status', ['Available', 'Reserved', 'Broken', 'Inactive'])->default('Available');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');

            $table->unique(['ShowtimeId', 'SeatId'], 'uq_showtime_seat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimeseats');
    }
};
