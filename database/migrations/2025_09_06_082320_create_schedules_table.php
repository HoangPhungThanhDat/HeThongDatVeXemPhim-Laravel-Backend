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
        Schema::create('schedules', function (Blueprint $table) {
            $table->integer('ScheduleId', true);
            $table->integer('MovieId')->index('idx_schedules_movie');
            $table->integer('RoomId')->index('idx_schedules_room');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->set('DaysOfWeek', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])->nullable();
            $table->time('StartTime');
            $table->time('EndTime');
            $table->decimal('Price', 10)->default(0);
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('schedules');
    }
};
