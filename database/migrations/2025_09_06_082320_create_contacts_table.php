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
        Schema::create('contacts', function (Blueprint $table) {
            $table->integer('ContactId', true);
            $table->integer('UserId')->nullable()->index('idx_contacts_user');
            $table->string('FullName');
            $table->string('Email');
            $table->string('Phone', 50)->nullable();
            $table->text('Message');
            $table->enum('Status', ['New', 'InProgress', 'Resolved', 'Closed'])->default('New')->index('idx_contacts_status');
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
        Schema::dropIfExists('contacts');
    }
};
