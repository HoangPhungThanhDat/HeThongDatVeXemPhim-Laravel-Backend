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
        Schema::create('movies', function (Blueprint $table) {
            $table->integer('MovieId', true);
            $table->string('Title')->index('idx_movies_title');
            $table->string('Slug')->unique('slug');
            $table->text('Description')->nullable();
            $table->integer('GenreId')->unsigned()->nullable()->index('fk_movies_genreid');
            $table->integer('Duration')->nullable();
            $table->date('ReleaseDate')->nullable();
            $table->string('PosterUrl', 500)->nullable();
            $table->string('TrailerUrl', 500)->nullable();
            $table->string('Language', 100)->nullable();
            $table->string('Rated', 20)->nullable();
            $table->enum('Status', ['ComingSoon', 'NowShowing', 'Ended'])->default('ComingSoon')->index('idx_movies_status');
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
        Schema::dropIfExists('movies');
    }
};
