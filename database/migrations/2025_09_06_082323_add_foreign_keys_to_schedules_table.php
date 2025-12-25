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
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign(['MovieId'], 'schedules_ibfk_1')->references(['MovieId'])->on('movies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['RoomId'], 'schedules_ibfk_2')->references(['RoomId'])->on('rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'schedules_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'schedules_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign('schedules_ibfk_1');
            $table->dropForeign('schedules_ibfk_2');
            $table->dropForeign('schedules_ibfk_3');
            $table->dropForeign('schedules_ibfk_4');
        });
    }
};
