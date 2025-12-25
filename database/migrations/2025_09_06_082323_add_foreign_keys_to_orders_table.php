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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign(['UserId'], 'orders_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['PromotionId'], 'orders_ibfk_2')->references(['PromotionId'])->on('promotions')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['CreatedBy'], 'orders_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'orders_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_ibfk_1');
            $table->dropForeign('orders_ibfk_2');
            $table->dropForeign('orders_ibfk_3');
            $table->dropForeign('orders_ibfk_4');
        });
    }
};
