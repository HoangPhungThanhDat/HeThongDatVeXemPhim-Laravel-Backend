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
        Schema::create('promotions', function (Blueprint $table) {
            $table->integer('PromotionId', true);
            $table->string('Title');
            $table->string('Code', 100)->nullable()->unique('code');
            $table->text('Description')->nullable();
            $table->string('ImageUrl', 500)->nullable();
            $table->enum('DiscountType', ['Percentage', 'FixedAmount']);
            $table->decimal('DiscountValue', 10)->default(0);
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->boolean('IsActive')->default(true)->index('idx_promotions_active');
            $table->enum('Status', ['Active', 'Inactive', 'Expired'])->default('Active');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');

            $table->index(['Code'], 'idx_promotions_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
