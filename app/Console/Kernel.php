<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ ĐÃ XÓA HOÀN TOÀN CÁC LỊCH CHẠY TỰ ĐỘNG CẬP NHẬT TRẠNG THÁI PHIM
        // Trạng thái phim giờ đây sẽ được cập nhật thủ công từ giao diện Admin.
        
        // Nếu sau này bạn cần thêm các lịch chạy ngầm khác (ví dụ: xóa vé rác, gửi mail...),
        // bạn có thể thêm vào bên dưới dòng này.
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}