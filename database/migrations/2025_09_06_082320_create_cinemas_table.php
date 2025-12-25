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
        Schema::create('cinemas', function (Blueprint $table) {
            $table->integer('CinemaId', true);
            $table->string('Name');
            $table->string('Address', 500)->nullable();
            $table->string('City', 200)->nullable()->index('idx_cinemas_city');
            $table->string('Phone', 50)->nullable();
            $table->string('Email')->nullable();
            $table->string('ImageUrl', 500)->nullable();
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
        Schema::dropIfExists('cinemas');
    }
};
