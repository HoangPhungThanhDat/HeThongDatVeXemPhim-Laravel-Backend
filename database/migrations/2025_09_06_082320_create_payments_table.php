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
        Schema::create('payments', function (Blueprint $table) {
            $table->integer('PaymentId', true);
            $table->integer('TicketId')->nullable()->index('idx_payments_ticket');
            $table->decimal('Amount', 10)->default(0);
            $table->enum('PaymentMethod', ['CreditCard', 'Momo', 'ZaloPay', 'Cash', 'Other'])->default('Other');
            $table->enum('PaymentStatus', ['Success', 'Failed', 'Pending'])->default('Pending')->index('idx_payments_status');
            $table->dateTime('PaymentDate')->nullable();
            $table->enum('Status', ['Active', 'Inactive'])->default('Active');
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
        Schema::dropIfExists('payments');
    }
};
