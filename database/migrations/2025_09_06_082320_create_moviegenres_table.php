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
        Schema::create('moviegenres', function (Blueprint $table) {
            $table->integer('MovieGenreId', true);
            $table->integer('MovieId')->index('idx_moviegenres_movie');
            $table->integer('GenreId')->index('genreid');
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');

            $table->unique(['MovieId', 'GenreId'], 'uq_movie_genre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moviegenres');
    }
};
