<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper: Kiểm tra xem index đã tồn tại chưa để tránh lỗi trùng lặp
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bảng users - Tối ưu cho việc JOIN
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'idx_users_id')) {
                $table->index('UserId', 'idx_users_id');
            }
        });

        // 2. Bảng showtimes - Tối ưu cho việc JOIN
        Schema::table('showtimes', function (Blueprint $table) {
            if (!$this->hasIndex('showtimes', 'idx_showtimes_id')) {
                $table->index('ShowtimeId', 'idx_showtimes_id');
            }
        });
        
        // 3. Bảng tickets - Thêm index cho Status để lọc nhanh hơn
        Schema::table('tickets', function (Blueprint $table) {
            if (!$this->hasIndex('tickets', 'idx_tickets_status')) {
                $table->index('Status', 'idx_tickets_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->hasIndex('users', 'idx_users_id')) {
                $table->dropIndex('idx_users_id');
            }
        });

        Schema::table('showtimes', function (Blueprint $table) {
            if ($this->hasIndex('showtimes', 'idx_showtimes_id')) {
                $table->dropIndex('idx_showtimes_id');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if ($this->hasIndex('tickets', 'idx_tickets_status')) {
                $table->dropIndex('idx_tickets_status');
            }
        });
    }
};