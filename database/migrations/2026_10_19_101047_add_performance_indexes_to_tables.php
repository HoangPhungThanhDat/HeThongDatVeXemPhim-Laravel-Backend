<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper: kiểm tra index đã tồn tại chưa (tránh lỗi duplicate).
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select(
                "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                [$indexName]
            );
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
        // ============================================================
        // 🔴 NHÓM 1: BẮT BUỘC — Query chậm 2s trong log
        // ============================================================

        // orders — lọc theo user + sắp xếp mới nhất
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->hasIndex('orders', 'idx_orders_user_date')) {
                $table->index(['UserId', 'OrderDate'], 'idx_orders_user_date');
            }
            if (!$this->hasIndex('orders', 'idx_orders_status')) {
                $table->index('Status', 'idx_orders_status');
            }
        });

        // orderdetails — JOIN từ order (CHẬM NHẤT - 2s)
        Schema::table('orderdetails', function (Blueprint $table) {
            if (!$this->hasIndex('orderdetails', 'idx_orderdetails_order')) {
                $table->index('OrderId', 'idx_orderdetails_order');
            }
            if (!$this->hasIndex('orderdetails', 'idx_orderdetails_ticket')) {
                $table->index('TicketId', 'idx_orderdetails_ticket');
            }
            if (!$this->hasIndex('orderdetails', 'idx_orderdetails_item')) {
                $table->index('ItemId', 'idx_orderdetails_item');
            }
        });

        // tickets — JOIN từ order, showtime, seat
        Schema::table('tickets', function (Blueprint $table) {
            if (!$this->hasIndex('tickets', 'idx_tickets_showtime')) {
                $table->index('ShowtimeId', 'idx_tickets_showtime');
            }
            if (!$this->hasIndex('tickets', 'idx_tickets_seat')) {
                $table->index('SeatId', 'idx_tickets_seat');
            }
            if (!$this->hasIndex('tickets', 'idx_tickets_user')) {
                $table->index('UserId', 'idx_tickets_user');
            }
        });

        // ============================================================
        // 🟡 NHÓM 2: NÊN CÓ — Query 500ms-1s trong log
        // ============================================================

        // showtimes — JOIN từ ticket, lọc theo phim/phòng
        Schema::table('showtimes', function (Blueprint $table) {
            if (!$this->hasIndex('showtimes', 'idx_showtimes_movie')) {
                $table->index('MovieId', 'idx_showtimes_movie');
            }
            if (!$this->hasIndex('showtimes', 'idx_showtimes_room')) {
                $table->index('RoomId', 'idx_showtimes_room');
            }
            if (!$this->hasIndex('showtimes', 'idx_showtimes_time')) {
                $table->index('StartTime', 'idx_showtimes_time');
            }
        });

        // schedules — lọc theo phim, phòng
        Schema::table('schedules', function (Blueprint $table) {
            if (!$this->hasIndex('schedules', 'idx_schedules_movie')) {
                $table->index('MovieId', 'idx_schedules_movie');
            }
            if (!$this->hasIndex('schedules', 'idx_schedules_room')) {
                $table->index('RoomId', 'idx_schedules_room');
            }
        });

        // movies — lọc theo status, release date
        Schema::table('movies', function (Blueprint $table) {
            if (!$this->hasIndex('movies', 'idx_movies_status')) {
                $table->index('Status', 'idx_movies_status');
            }
            if (!$this->hasIndex('movies', 'idx_movies_release')) {
                $table->index('ReleaseDate', 'idx_movies_release');
            }
        });

        // rooms — lọc theo rạp
        Schema::table('rooms', function (Blueprint $table) {
            if (!$this->hasIndex('rooms', 'idx_rooms_cinema')) {
                $table->index('CinemaId', 'idx_rooms_cinema');
            }
        });

        // ============================================================
        // 🟢 NHÓM 3: TÙY CHỌN — Cho chức năng tương lai
        // ============================================================

        // promotions
        Schema::table('promotions', function (Blueprint $table) {
            if (!$this->hasIndex('promotions', 'idx_promotions_status')) {
                $table->index('Status', 'idx_promotions_status');
            }
            if (!$this->hasIndex('promotions', 'idx_promotions_dates')) {
                $table->index(['StartDate', 'EndDate'], 'idx_promotions_dates');
            }
        });

        // news
        Schema::table('news', function (Blueprint $table) {
            if (!$this->hasIndex('news', 'idx_news_status')) {
                $table->index('Status', 'idx_news_status');
            }
            if (!$this->hasIndex('news', 'idx_news_created')) {
                $table->index('CreatedAt', 'idx_news_created');
            }
        });

        // reviews
        Schema::table('reviews', function (Blueprint $table) {
            if (!$this->hasIndex('reviews', 'idx_reviews_movie')) {
                $table->index('MovieId', 'idx_reviews_movie');
            }
            if (!$this->hasIndex('reviews', 'idx_reviews_user')) {
                $table->index('UserId', 'idx_reviews_user');
            }
        });

        // seats
        Schema::table('seats', function (Blueprint $table) {
            if (!$this->hasIndex('seats', 'idx_seats_room')) {
                $table->index('RoomId', 'idx_seats_room');
            }
        });

        // showtimeseats
        Schema::table('showtimeseats', function (Blueprint $table) {
            if (!$this->hasIndex('showtimeseats', 'idx_showtimeseats_showtime')) {
                $table->index('ShowtimeId', 'idx_showtimeseats_showtime');
            }
            if (!$this->hasIndex('showtimeseats', 'idx_showtimeseats_seat')) {
                $table->index('SeatId', 'idx_showtimeseats_seat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $drops = [
            'orders'        => ['idx_orders_user_date', 'idx_orders_status'],
            'orderdetails'  => ['idx_orderdetails_order', 'idx_orderdetails_ticket', 'idx_orderdetails_item'],
            'tickets'       => ['idx_tickets_showtime', 'idx_tickets_seat', 'idx_tickets_user'],
            'showtimes'     => ['idx_showtimes_movie', 'idx_showtimes_room', 'idx_showtimes_time'],
            'schedules'     => ['idx_schedules_movie', 'idx_schedules_room'],
            'movies'        => ['idx_movies_status', 'idx_movies_release'],
            'rooms'         => ['idx_rooms_cinema'],
            'promotions'    => ['idx_promotions_status', 'idx_promotions_dates'],
            'news'          => ['idx_news_status', 'idx_news_created'],
            'reviews'       => ['idx_reviews_movie', 'idx_reviews_user'],
            'seats'         => ['idx_seats_room'],
            'showtimeseats' => ['idx_showtimeseats_showtime', 'idx_showtimeseats_seat'],
        ];

        foreach ($drops as $table => $indexes) {
            Schema::table($table, function (Blueprint $blueprint) use ($indexes) {
                foreach ($indexes as $index) {
                    try {
                        $blueprint->dropIndex($index);
                    } catch (\Exception $e) {
                        // Bỏ qua nếu index không tồn tại
                    }
                }
            });
        }
    }
};