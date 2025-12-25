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
        Schema::table('auditlogs', function (Blueprint $table) {
            $table->foreign(['UserId'], 'auditlogs_ibfk_1')->references(['UserId'])->on('users')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auditlogs', function (Blueprint $table) {
            $table->dropForeign('auditlogs_ibfk_1');
        });
    }
};
