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
        Schema::table('moviegenres', function (Blueprint $table) {
            $table->foreign(['MovieId'], 'moviegenres_ibfk_1')->references(['MovieId'])->on('movies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['GenreId'], 'moviegenres_ibfk_2')->references(['GenreId'])->on('genres')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'moviegenres_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'moviegenres_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moviegenres', function (Blueprint $table) {
            $table->dropForeign('moviegenres_ibfk_1');
            $table->dropForeign('moviegenres_ibfk_2');
            $table->dropForeign('moviegenres_ibfk_3');
            $table->dropForeign('moviegenres_ibfk_4');
        });
    }
};
