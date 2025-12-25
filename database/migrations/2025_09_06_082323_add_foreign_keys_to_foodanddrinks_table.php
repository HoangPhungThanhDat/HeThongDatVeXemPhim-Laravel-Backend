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
        Schema::table('foodanddrinks', function (Blueprint $table) {
            $table->foreign(['CreatedBy'], 'foodanddrinks_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'foodanddrinks_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foodanddrinks', function (Blueprint $table) {
            $table->dropForeign('foodanddrinks_ibfk_1');
            $table->dropForeign('foodanddrinks_ibfk_2');
        });
    }
};
