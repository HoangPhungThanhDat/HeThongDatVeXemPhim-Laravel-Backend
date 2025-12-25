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
        Schema::table('seats', function (Blueprint $table) {
            $table->foreign(['RoomId'], 'seats_ibfk_1')->references(['RoomId'])->on('rooms')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'seats_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'seats_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropForeign('seats_ibfk_1');
            $table->dropForeign('seats_ibfk_2');
            $table->dropForeign('seats_ibfk_3');
        });
    }
};
