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
        Schema::create('users', function (Blueprint $table) {
            $table->integer('UserId', true);
            $table->string('FullName', 200);
            $table->string('Email')->unique('email');
            $table->string('PasswordHash', 512);
            $table->string('PhoneNumber', 50)->nullable();
            $table->enum('Gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->integer('RoleId')->nullable()->index('idx_users_role');
            $table->enum('Status', ['Active', 'Inactive', 'Banned'])->default('Active');
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrentOnUpdate()->useCurrent();
            $table->integer('CreatedBy')->nullable()->index('createdby');
            $table->integer('UpdatedBy')->nullable()->index('updatedby');

            $table->index(['Email'], 'idx_users_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
