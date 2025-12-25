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
        Schema::table('memberships', function (Blueprint $table) {
            $table->foreign(['UserId'], 'memberships_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['CreatedBy'], 'memberships_ibfk_2')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['UpdatedBy'], 'memberships_ibfk_3')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropForeign('memberships_ibfk_1');
            $table->dropForeign('memberships_ibfk_2');
            $table->dropForeign('memberships_ibfk_3');
        });
    }
};
