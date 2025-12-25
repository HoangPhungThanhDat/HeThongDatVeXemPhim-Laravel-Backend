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
        Schema::table('staff', function (Blueprint $table) {
            $table->foreign(['CinemaId'], 'staff_ibfk_1')->references(['CinemaId'])->on('cinemas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'staff_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'staff_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign('staff_ibfk_1');
            $table->dropForeign('staff_ibfk_2');
            $table->dropForeign('staff_ibfk_3');
        });
    }
};
