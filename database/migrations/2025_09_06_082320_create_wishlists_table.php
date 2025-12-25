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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->integer('WishlistId', true);
            $table->integer('UserId')->index('idx_wishlist_user');
            $table->integer('MovieId')->index('movieid');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');

            $table->unique(['UserId', 'MovieId'], 'uq_wishlist_user_movie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
