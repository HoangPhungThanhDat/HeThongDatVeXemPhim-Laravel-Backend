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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign(['UserId'], 'reviews_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['MovieId'], 'reviews_ibfk_2')->references(['MovieId'])->on('movies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'reviews_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'reviews_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_ibfk_1');
            $table->dropForeign('reviews_ibfk_2');
            $table->dropForeign('reviews_ibfk_3');
            $table->dropForeign('reviews_ibfk_4');
        });
    }
};
