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
        Schema::create('showtimes', function (Blueprint $table) {
            $table->integer('ShowtimeId', true);
            $table->integer('MovieId')->index('idx_showtimes_movie');
            $table->integer('RoomId')->index('idx_showtimes_room');
            $table->dateTime('StartTime')->index('idx_showtimes_start');
            $table->dateTime('EndTime');
            $table->decimal('Price', 10)->default(0);
            $table->enum('Status', ['Scheduled', 'Cancelled', 'Finished'])->default('Scheduled');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
