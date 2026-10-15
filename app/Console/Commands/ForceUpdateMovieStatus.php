<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Movie;
use Carbon\Carbon;

class ForceUpdateMovieStatus extends Command
{
    protected $signature = 'movies:force-update-status';
    protected $description = 'Force update movie status based on release date (ignores showtimes)';

    public function handle()
    {
        $this->info('🔄 FORCE UPDATE TRẠNG THÁI PHIM');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('⏰ Thời gian: ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        $movies = Movie::all();
        $today = Carbon::now()->startOfDay();
        $updatedCount = 0;
        $updates = [];

        $this->info('📊 Tổng số phim: ' . $movies->count());
        $this->newLine();

        foreach ($movies as $movie) {
            $releaseDate = Carbon::parse($movie->ReleaseDate)->startOfDay();
            
            // ✅ Sửa: Tính số ngày đã qua (lấy giá trị tuyệt đối)
            $daysSinceRelease = abs($today->diffInDays($releaseDate));
            
            $oldStatus = $movie->Status;
            
            // ✅ Logic đúng
            if ($releaseDate->gt($today)) {
                $newStatus = 'ComingSoon';
            } elseif ($daysSinceRelease >= 60) {
                $newStatus = 'Ended';
            } else {
                $newStatus = 'NowShowing';
            }
            
            // Debug
            $this->line("📝 Phim: {$movie->Title}");
            $this->line("   Ngày chiếu: {$releaseDate->format('d/m/Y')}");
            $this->line("   Hôm nay: {$today->format('d/m/Y')}");
            $this->line("   Số ngày đã qua: {$daysSinceRelease}");
            $this->line("   Trạng thái hiện tại: {$oldStatus}");
            $this->line("   Trạng thái mới: {$newStatus}");
            
            if ($oldStatus !== $newStatus) {
                $movie->Status = $newStatus;
                $movie->save();
                $updatedCount++;
                $updates[] = [
                    'movie_id' => $movie->MovieId,
                    'title' => $movie->Title,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'days_since_release' => $daysSinceRelease
                ];
                $this->line("   ✅ ĐÃ CẬP NHẬT!");
            } else {
                $this->line("   ➖ Không thay đổi");
            }
            $this->newLine();
        }

        // Hiển thị kết quả
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        if ($updatedCount > 0) {
            $this->info("✅ Đã force update {$updatedCount} phim:");
            $this->table(
                ['ID', 'Tên phim', 'Trạng thái cũ', 'Trạng thái mới', 'Số ngày'],
                array_map(function ($update) {
                    return [
                        $update['movie_id'],
                        $update['title'],
                        $update['old_status'],
                        $update['new_status'],
                        $update['days_since_release']
                    ];
                }, $updates)
            );
        } else {
            $this->info('💡 Không có phim nào cần cập nhật.');
        }

        $this->newLine();
        $this->info('✨ HOÀN THÀNH!');
        
        return Command::SUCCESS;
    }
}