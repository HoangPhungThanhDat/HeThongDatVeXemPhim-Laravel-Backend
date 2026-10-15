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
        // ✅ Cách 1: Chạy Artisan Command mỗi ngày lúc 00:00
        $schedule->command('movies:update-status')->daily();
        
        // ✅ Cách 2: Chạy mỗi 6 giờ (nếu cần cập nhật thường xuyên hơn)
        // $schedule->command('movies:update-status')->everySixHours();
        
        // ✅ Cách 3: Chạy mỗi giờ (nếu có nhiều thay đổi)
        // $schedule->command('movies:update-status')->hourly();
        
        // ✅ Cách 4: Gọi API nội bộ
        // $schedule->call(function () {
        //     try {
        //         $token = config('app.scheduler_token', '');
        //         $response = Http::withHeaders([
        //             'Authorization' => 'Bearer ' . $token,
        //             'Accept' => 'application/json'
        //         ])->post(config('app.url') . '/api/movies/auto-update-status');
                
        //         if ($response->successful()) {
        //             Log::info('Auto update movie status executed successfully', $response->json());
        //         } else {
        //             Log::error('Auto update movie status failed', [
        //                 'status' => $response->status(),
        //                 'body' => $response->body()
        //             ]);
        //         }
        //     } catch (\Exception $e) {
        //         Log::error('Auto update movie status exception: ' . $e->getMessage());
        //     }
        // })->daily();
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