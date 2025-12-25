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
        Schema::create('loginhistory', function (Blueprint $table) {
            $table->integer('LoginId', true);
            $table->integer('UserId')->nullable()->index('idx_loginhistory_user');
            $table->dateTime('LoginTime')->useCurrent()->index('idx_loginhistory_time');
            $table->string('IpAddress', 100)->nullable();
            $table->string('DeviceInfo', 500)->nullable();
            $table->enum('Status', ['Success', 'Failed']);
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
        Schema::dropIfExists('loginhistory');
    }
};
