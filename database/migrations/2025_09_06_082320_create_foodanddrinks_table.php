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
        Schema::create('foodanddrinks', function (Blueprint $table) {
            $table->integer('ItemId', true);
            $table->string('Name')->index('idx_food_name');
            $table->text('Description')->nullable();
            $table->decimal('Price', 10)->default(0);
            $table->string('ImageUrl', 500)->nullable();
            $table->boolean('IsAvailable')->default(true)->index('idx_food_available');
            $table->enum('Status', ['Active', 'Inactive', 'OutOfStock'])->default('Active');
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
        Schema::dropIfExists('foodanddrinks');
    }
};
