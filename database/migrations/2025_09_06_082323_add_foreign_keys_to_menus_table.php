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
        Schema::table('menus', function (Blueprint $table) {
            $table->foreign(['UserId'], 'menus_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['ParentId'], 'menus_ibfk_2')->references(['MenuId'])->on('menus')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['CreatedBy'], 'menus_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'menus_ibfk_4')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign('menus_ibfk_1');
            $table->dropForeign('menus_ibfk_2');
            $table->dropForeign('menus_ibfk_3');
            $table->dropForeign('menus_ibfk_4');
        });
    }
};
