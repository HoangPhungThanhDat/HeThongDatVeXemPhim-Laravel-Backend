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
        Schema::table('orderdetails', function (Blueprint $table) {
            $table->foreign(['OrderId'], 'orderdetails_ibfk_1')->references(['OrderId'])->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['TicketId'], 'orderdetails_ibfk_2')->references(['TicketId'])->on('tickets')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['ItemId'], 'orderdetails_ibfk_3')->references(['ItemId'])->on('foodanddrinks')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['CreatedBy'], 'orderdetails_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'orderdetails_ibfk_5')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orderdetails', function (Blueprint $table) {
            $table->dropForeign('orderdetails_ibfk_1');
            $table->dropForeign('orderdetails_ibfk_2');
            $table->dropForeign('orderdetails_ibfk_3');
            $table->dropForeign('orderdetails_ibfk_4');
            $table->dropForeign('orderdetails_ibfk_5');
        });
    }
};
