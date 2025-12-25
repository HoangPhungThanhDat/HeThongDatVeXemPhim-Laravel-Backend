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
        Schema::create('orders', function (Blueprint $table) {
            $table->integer('OrderId', true);
            $table->integer('UserId')->nullable()->index('idx_orders_user');
            $table->dateTime('OrderDate')->useCurrent();
            $table->decimal('TotalAmount', 12)->default(0);
            $table->integer('PromotionId')->nullable()->index('promotionid');
            $table->enum('Status', ['Pending', 'Paid', 'Cancelled', 'Completed'])->default('Pending')->index('idx_orders_status');
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
        Schema::dropIfExists('orders');
    }
};
