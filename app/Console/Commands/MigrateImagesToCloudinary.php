<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Banner;
use App\Models\Movie;
use App\Models\Cinema;
use App\Models\Foodanddrink;
use App\Models\News;
use App\Models\Promotion;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MigrateImagesToCloudinary extends Command
{
    protected $signature = 'migrate:images 
                            {--model=all : Chỉ migrate cho model cụ thể (banner, movie, cinema, food, news, promotion)} 
                            {--force : Bỏ qua xác nhận}
                            {--dry-run : Chạy thử không upload thực tế}';
    
    protected $description = 'Migrate all local images to Cloudinary';

    protected $cloudinary;

    public function __construct()
    {
        parent::__construct();
        
        // Khởi tạo Cloudinary từ config (đọc từ .env)
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => config('cloudinary.secure', true),
            ],
        ]);
    }

    protected $models = [
        'banner' => [
            'model' => Banner::class,
            'field' => 'ImageUrl',
            'folder' => 'banners',
            'table' => 'banners',
        ],
        'movie' => [
            'model' => Movie::class,
            'field' => 'PosterUrl',
            'folder' => 'movies/posters',
            'table' => 'movies',
        ],
        'cinema' => [
            'model' => Cinema::class,
            'field' => 'ImageUrl',
            'folder' => 'cinemas',
            'table' => 'cinemas',
        ],
        'food' => [
            'model' => Foodanddrink::class,
            'field' => 'ImageUrl',
            'folder' => 'foodanddrinks',
            'table' => 'foodanddrinks',
        ],
        'news' => [
            'model' => News::class,
            'field' => 'ImageUrl',
            'folder' => 'news',
            'table' => 'news',
        ],
        'promotion' => [
            'model' => Promotion::class,
            'field' => 'ImageUrl',
            'folder' => 'promotions',
            'table' => 'promotions',
        ],
    ];

    public function handle()
    {
        $this->info('🚀 Bắt đầu migrate ảnh lên Cloudinary...');
        $this->newLine();
        
        $modelOption = $this->option('model');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE: Sẽ không upload thực tế');
            $this->newLine();
        }

        if (!$force && !$this->confirm('⚠️ Bạn có chắc muốn migrate tất cả ảnh lên Cloudinary?')) {
            $this->info('❌ Hủy bỏ.');
            return;
        }

        $modelsToMigrate = $modelOption === 'all' 
            ? array_keys($this->models) 
            : [$modelOption];

        $totalMigrated = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        foreach ($modelsToMigrate as $modelKey) {
            if (!isset($this->models[$modelKey])) {
                $this->error("❌ Model '$modelKey' không tồn tại!");
                continue;
            }

            $config = $this->models[$modelKey];
            $this->info("📁 Đang xử lý: " . class_basename($config['model']));
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            
            $result = $this->migrateModel($config, $dryRun);
            $totalMigrated += $result['migrated'];
            $totalFailed += $result['failed'];
            $totalSkipped += $result['skipped'];
            
            $this->newLine();
        }

        $this->newLine();
        $this->line("═══════════════════════════════════════════════");
        $this->info('✅ HOÀN THÀNH!');
        $this->line("📊 Tổng kết:");
        $this->line("   ✅ Đã migrate: {$totalMigrated}");
        $this->line("   ⏭️ Bỏ qua: {$totalSkipped}");
        $this->line("   ❌ Lỗi: {$totalFailed}");
        $this->line("═══════════════════════════════════════════════");
        
        if ($totalFailed > 0) {
            $this->warn('⚠️ Có ' . $totalFailed . ' ảnh bị lỗi. Kiểm tra log để biết chi tiết.');
        }
    }

    protected function migrateModel($config, $dryRun = false)
    {
        $model = $config['model'];
        $field = $config['field'];
        $folder = $config['folder'];
        $table = $config['table'];
        
        $records = $model::whereNotNull($field)->get();
        $total = $records->count();
        
        $this->info("📊 Tìm thấy {$total} bản ghi có ảnh trong bảng {$table}");
        
        $migrated = 0;
        $failed = 0;
        $skipped = 0;
        
        if ($total === 0) {
            return ['migrated' => 0, 'failed' => 0, 'skipped' => 0];
        }
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($records as $record) {
            $bar->advance();
            
            try {
                $oldPath = $record->$field;
                
                // Bỏ qua nếu đã là URL Cloudinary
                if (strpos($oldPath, 'cloudinary.com') !== false) {
                    $skipped++;
                    continue;
                }
                
                // Tìm đường dẫn file
                $localPath = $this->findLocalPath($oldPath);
                
                if (!$localPath) {
                    $this->error("\n❌ Không tìm thấy file: {$oldPath}");
                    $failed++;
                    continue;
                }
                
                // Nếu là dry-run thì chỉ hiển thị
                if ($dryRun) {
                    $this->line("\n   📝 Sẽ upload: {$localPath} (from: {$oldPath})");
                    $migrated++;
                    continue;
                }
                
                // Upload lên Cloudinary
                $uploaded = $this->uploadToCloudinary($localPath, $folder);
                
                if ($uploaded) {
                    // Cập nhật URL trong database
                    $record->$field = $uploaded;
                    $record->save();
                    
                    $migrated++;
                    $this->line("\n   ✅ Upload thành công: " . $uploaded);
                } else {
                    $failed++;
                }
                
            } catch (\Exception $e) {
                $this->error("\n❌ Lỗi: " . $e->getMessage());
                Log::error("Lỗi migrate ảnh: " . $e->getMessage(), [
                    'record_id' => $record->{$record->getKeyName()},
                    'field' => $field,
                    'path' => $oldPath ?? null
                ]);
                $failed++;
            }
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("✅ {$migrated} ảnh đã migrate, {$skipped} bỏ qua, {$failed} lỗi");
        
        return ['migrated' => $migrated, 'failed' => $failed, 'skipped' => $skipped];
    }

    protected function findLocalPath($url)
    {
        $fileName = basename($url);
        
        $path = parse_url($url, PHP_URL_PATH);
        
        // Trường hợp 1: /storage/uploads/banners/xxx.jpg
        if (strpos($path, '/storage/') !== false) {
            $cleanPath = str_replace('/storage/', '', $path);
            if (Storage::disk('public')->exists($cleanPath)) {
                return $cleanPath;
            }
        }
        
        // Trường hợp 2: /uploads/banners/xxx.jpg
        if (strpos($path, '/uploads/') !== false) {
            $cleanPath = ltrim($path, '/');
            if (Storage::disk('public')->exists($cleanPath)) {
                return $cleanPath;
            }
        }
        
        // Trường hợp 3: Tìm trong các thư mục uploads
        $folders = [
            'uploads/banners', 'uploads/movies', 'uploads/cinemas', 
            'uploads/foodanddrinks', 'uploads/news', 'uploads/promotions'
        ];
        
        foreach ($folders as $folder) {
            $testPath = $folder . '/' . $fileName;
            if (Storage::disk('public')->exists($testPath)) {
                return $testPath;
            }
        }
        
        return null;
    }

    protected function uploadToCloudinary($localPath, $folder)
    {
        try {
            $fullPath = storage_path("app/public/{$localPath}");
            
            if (!file_exists($fullPath)) {
                $this->error("\n❌ File không tồn tại: {$fullPath}");
                return null;
            }
            
            // Upload lên Cloudinary bằng SDK trực tiếp
            $result = $this->cloudinary->uploadApi()->upload($fullPath, [
                'folder' => $folder,
                'public_id' => pathinfo($localPath, PATHINFO_FILENAME) . '_' . time(),
                'overwrite' => false,
                'use_filename' => true,
                'unique_filename' => true,
                'transformation' => [
                    'quality' => 'auto:best',
                    'fetch_format' => 'auto',
                    'flags' => 'progressive'
                ]
            ]);
            
            if (isset($result['secure_url'])) {
                return $result['secure_url'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->error("\n❌ Upload lỗi: " . $e->getMessage());
            Log::error("Upload Cloudinary lỗi: " . $e->getMessage(), [
                'local_path' => $localPath,
                'folder' => $folder
            ]);
            return null;
        }
    }
}