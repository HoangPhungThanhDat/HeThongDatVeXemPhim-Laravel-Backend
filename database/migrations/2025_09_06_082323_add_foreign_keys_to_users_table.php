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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['RoleId'], 'users_ibfk_1')->references(['RoleId'])->on('roles')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['CreatedBy'], 'users_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'users_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_ibfk_1');
            $table->dropForeign('users_ibfk_2');
            $table->dropForeign('users_ibfk_3');
        });
    }
};
