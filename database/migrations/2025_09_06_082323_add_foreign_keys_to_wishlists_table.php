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
        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreign(['UserId'], 'wishlists_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['MovieId'], 'wishlists_ibfk_2')->references(['MovieId'])->on('movies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign('wishlists_ibfk_1');
            $table->dropForeign('wishlists_ibfk_2');
        });
    }
};
