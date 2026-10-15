<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MovieService;
use Illuminate\Support\Str;

class UpdateMovieStatus extends Command
{
    protected $signature = 'movies:update-status
                            {--force : Force update all movies even if no change}';
    
    protected $description = 'Tự động cập nhật trạng thái phim dựa trên ngày chiếu và suất chiếu';

    protected $movieService;

    public function __construct(MovieService $movieService)
    {
        parent::__construct();
        $this->movieService = $movieService;
    }

    public function handle()
    {
        $this->newLine();
        $this->info('🔄 BẮT ĐẦU CẬP NHẬT TRẠNG THÁI PHIM');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('⏰ Thời gian: ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
        
        $this->info('📊 Đang kiểm tra tất cả phim...');
        
        $result = $this->movieService->autoUpdateStatus();
        
        $this->newLine();
        
        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            
            if (!empty($result['updates'])) {
                $this->newLine();
                $this->line('📋 CHI TIẾT CẬP NHẬT:');
                $this->line('─────────────────────────────────────');
                
                $rows = array_map(function ($update) {
                    return [
                        $update['movie_id'],
                        Str::limit($update['title'], 35),
                        $this->formatStatus($update['old_status']),
                        $this->formatStatus($update['new_status']),
                        $update['old_status'] !== $update['new_status'] ? '✅' : '➖'
                    ];
                }, $result['updates']);
                
                $this->table(
                    ['ID', 'Tên phim', 'Trạng thái cũ', 'Trạng thái mới', 'Thay đổi'],
                    $rows
                );
                
                $this->newLine();
                $this->info('📈 Tổng số phim đã cập nhật: ' . count($result['updates']));
            } else {
                $this->info('💡 Không có phim nào cần cập nhật trạng thái.');
            }
            
            $this->newLine();
            $this->info('✨ HOÀN THÀNH!');
        } else {
            $this->error('❌ ' . $result['message']);
        }
        
        $this->newLine();
        
        return Command::SUCCESS;
    }

    /**
     * Format status for display
     */
    private function formatStatus(string $status): string
    {
        $map = [
            'NowShowing' => '🟢 Đang chiếu',
            'ComingSoon' => '🟡 Sắp chiếu',
            'Ended' => '🔴 Đã kết thúc'
        ];
        return $map[$status] ?? $status;
    }
}