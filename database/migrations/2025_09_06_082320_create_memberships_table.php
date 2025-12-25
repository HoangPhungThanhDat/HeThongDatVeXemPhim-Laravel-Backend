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
        Schema::create('memberships', function (Blueprint $table) {
            $table->integer('MembershipId', true);
            $table->integer('UserId')->index('idx_memberships_user');
            $table->enum('Level', ['Basic', 'Silver', 'Gold', 'Platinum'])->default('Basic');
            $table->integer('Points')->default(0);
            $table->date('StartDate');
            $table->date('EndDate')->nullable();
            $table->text('Benefits')->nullable();
            $table->enum('Status', ['Active', 'Expired', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('memberships');
    }
};
