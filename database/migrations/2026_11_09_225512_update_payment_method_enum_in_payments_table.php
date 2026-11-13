<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sửa cột PaymentMethod để thêm VNPAY vào danh sách ENUM
        DB::statement("ALTER TABLE payments MODIFY COLUMN PaymentMethod ENUM('CreditCard', 'Momo', 'ZaloPay', 'VNPAY', 'Cash') NOT NULL");
    }

    public function down(): void
    {
        // Khôi phục lại nếu cần
        DB::statement("ALTER TABLE payments MODIFY COLUMN PaymentMethod ENUM('CreditCard', 'Momo', 'ZaloPay', 'Cash') NOT NULL");
    }
};