<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // ⭐ Thêm Schedule

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ⭐ LÊN LỊCH DỌN DẸP LOG CŨ TỰ ĐỘNG
// Chạy lệnh xóa log cũ hơn 30 ngày vào lúc 1:00 sáng mỗi ngày
Schedule::command('auditlogs:clean')->dailyAt('01:00');