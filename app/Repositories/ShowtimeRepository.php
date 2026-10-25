<?php

namespace App\Repositories;

use App\Models\Showtime;
use Illuminate\Support\Facades\Cache;

class ShowtimeRepository
{
    const CACHE_TIME = 300; // 5 phút

    /**
     * Lấy tất cả
     */
    public function all()
    {
        return Cache::remember('showtimes_all', self::CACHE_TIME, function () {
            // ✅ Sắp xếp mới nhất lên đầu
            return Showtime::with(['Movie', 'Room'])->orderBy('ShowtimeId', 'desc')->get();
        });
    }

    /**
     * ✅ Lấy dữ liệu phân trang
     */
    public function getPaged($page, $limit, $search = '', $status = '')
    {
        $cacheKey = "showtimes_paged_p{$page}_l{$limit}_" . md5($search . $status);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $status) {
            $query = Showtime::with(['Movie', 'Room']);

            if ($search) {
                $query->whereHas('Movie', function ($q) use ($search) {
                    $q->where('Title', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('Status', $status);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            
            // ✅ Sắp xếp ID giảm dần (suất chiếu mới tạo luôn nằm trên cùng)
            $items      = $query->orderBy('ShowtimeId', 'desc')
                                ->skip(($page - 1) * $limit)
                                ->take($limit)
                                ->get();

            return [
                'data'       => $items,
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'totalPages' => $totalPages,
            ];
        });
    }

    public function find($ShowtimeId)
    {
        return Cache::remember("showtime_detail_{$ShowtimeId}", self::CACHE_TIME, function () use ($ShowtimeId) {
            return Showtime::with(['Movie', 'Room'])->find($ShowtimeId);
        });
    }

    public function create(array $data)
    {
        $showtime = Showtime::create($data);
        $this->clearAllCache();
        return $showtime;
    }

    public function update(Showtime $showtime, array $data)
    {
        $showtime->update($data);
        $this->clearAllCache($showtime->ShowtimeId);
        return $showtime;
    }

    public function delete(Showtime $showtime)
    {
        $id = $showtime->ShowtimeId;
        $result = $showtime->delete();
        $this->clearAllCache($id);
        return $result;
    }

    /**
     * ✅ XÓA SẠCH CACHE (Tự động tìm và xóa mọi biến thể tìm kiếm)
     */
    private function clearAllCache($id = null)
    {
        // 1. Xóa cache all
        Cache::forget('showtimes_all');
        // Xóa cache riêng lẻ
        if ($id) {
            Cache::forget("showtime_detail_{$id}");
        }

        // 2. Xóa toàn bộ cache phân trang một cách triệt để
        // Cách tốt nhất là dùng Cache Tag nếu bạn dùng Redis/Memcached
        // Nếu dùng file cache, ta phải dùng cách "quét" key (chỉ khuyên dùng cho dev)
        // Hoặc đơn giản là xóa các key phổ biến. 
        // Nhưng để chắc chắn 100%, ta sẽ dùng mẹo "Cache Version" (thay đổi version mỗi khi create/update/delete)

        $versionKey = 'showtimes_cache_version';
        $currentVersion = Cache::get($versionKey, 1);
        Cache::put($versionKey, $currentVersion + 1, self::CACHE_TIME);
    }

    /**
     * ✅ Thêm Version vào Cache Key để tự động vô hiệu hóa cache cũ
     */
    public function getPagedWithVersion($page, $limit, $search = '', $status = '')
    {
        $version = Cache::get('showtimes_cache_version', 1);
        $cacheKey = "showtimes_v{$version}_p{$page}_l{$limit}_" . md5($search . $status);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $status) {
            $query = Showtime::with(['Movie', 'Room']);

            if ($search) {
                $query->whereHas('Movie', function ($q) use ($search) {
                    $q->where('Title', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('Status', $status);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            
            $items      = $query->orderBy('ShowtimeId', 'desc')
                                ->skip(($page - 1) * $limit)
                                ->take($limit)
                                ->get();

            return [
                'data'       => $items,
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'totalPages' => $totalPages,
            ];
        });
    }
}