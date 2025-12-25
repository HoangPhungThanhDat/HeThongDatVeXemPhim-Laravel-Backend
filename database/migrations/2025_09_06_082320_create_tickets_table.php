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
        Schema::create('tickets', function (Blueprint $table) {
            $table->integer('TicketId', true);
            $table->integer('ShowtimeId');
            $table->integer('SeatId')->index('seatid');
            $table->integer('UserId')->nullable()->index('idx_tickets_user');
            $table->dateTime('BookingTime')->useCurrent();
            $table->enum('Status', ['Pending', 'Paid', 'Cancelled'])->default('Pending')->index('idx_tickets_status');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');

            $table->unique(['ShowtimeId', 'SeatId'], 'uq_ticket_showtime_seat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
