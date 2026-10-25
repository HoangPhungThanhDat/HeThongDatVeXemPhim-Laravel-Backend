<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chỉ chạy đo thời gian khi ở môi trường local (dev)
        if ($this->app->environment('local')) {
            
            // 1. Bắt đầu tính giờ khi request vừa vào
            $startTime = microtime(true);

            // 2. Khi request xử lý xong, tính thời gian và ghi vào log
            $this->app->terminating(function () use ($startTime) {
                $time = round((microtime(true) - $startTime) * 1000, 2);
                $method = request()->method();
                $path = '/' . ltrim(request()->path(), '/');
                $status = http_response_code();

                // Tạo chuỗi hiển thị
                $logMessage = sprintf("[%s] %s ~ %sms", $method, $path, $time);

                // Tự động thêm icon cảnh báo
                if ($time < 200) {
                    $logMessage = "NHANH: " . $logMessage;
                } elseif ($time < 500) {
                    $logMessage = "VUA: " . $logMessage;
                } else {
                    $logMessage = "CHAM: " . $logMessage;
                }

                // Ghi vào file storage/logs/laravel.log
                Log::info("{$logMessage} | Status: {$status}");
            });

            // 3. In ra các câu lệnh SQL bị chậm (nếu có)
            DB::listen(function ($query) {
                if ($query->time > 200) { // Chỉ báo nếu query > 200ms
                    Log::warning("🐢 SLOW QUERY ({$query->time}ms): {$query->sql}");
                }
            });
        }
    }
}