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
        Schema::create('seats', function (Blueprint $table) {
            $table->integer('SeatId', true);
            $table->integer('RoomId')->index('idx_seats_room');
            $table->char('Row', 3);
            $table->integer('Number');
            $table->enum('SeatType', ['Normal', 'VIP', 'Couple'])->default('Normal');
            $table->enum('Status', ['Available', 'Broken', 'Inactive'])->default('Available');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');

            $table->unique(['RoomId', 'Row', 'Number'], 'uq_seat_room_row_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
