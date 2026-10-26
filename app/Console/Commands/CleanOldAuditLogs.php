<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auditlog;
use Carbon\Carbon;

class CleanOldAuditLogs extends Command
{
    // Tên lệnh để chạy
    protected $signature = 'auditlogs:clean';

    // Mô tả
    protected $description = 'Xóa các bản ghi nhật ký cũ hơn 30 ngày';

    public function handle()
    {
        $date = Carbon::now()->subDays(30);
        
        // Xóa các log cũ
        $deletedCount = Auditlog::where('CreatedAt', '<', $date)->delete();

        $this->info("Đã dọn dẹp thành công {$deletedCount} bản ghi nhật ký cũ hơn 30 ngày.");
    }
}