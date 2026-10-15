<?php
// database/migrations/2026_10_15_214358_create_refresh_tokens_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();

            // ⭐ FK khớp với users.UserId (int(11) - không unsigned)
            $table->integer('user_id');

            // SHA-256 hash của refresh token
            $table->string('token_hash', 64)->unique();

            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // FK tới users.UserId
            $table->foreign('user_id')
                  ->references('UserId')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index(['user_id', 'revoked']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};