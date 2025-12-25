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
        Schema::create('auditlogs', function (Blueprint $table) {
            $table->integer('LogId', true);
            $table->integer('UserId')->nullable()->index('idx_auditlogs_user');
            $table->string('Action', 100);
            $table->text('Description')->nullable();
            $table->string('IpAddress', 100)->nullable();
            $table->string('DeviceInfo', 500)->nullable();
            $table->dateTime('CreatedAt')->useCurrent()->index('idx_auditlogs_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditlogs');
    }
};
