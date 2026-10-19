<?php

namespace App\Repositories;

use App\Models\Showtime;
use Illuminate\Support\Facades\Cache;

class ShowtimeRepository
{
    // Cache ngắn hơn vì suất chiếu thay đổi liên tục
    const CACHE_TIME = 300; // 5 phút

    /**
     * Lấy tất cả - ✅ ĐÃ CACHE
     */
    public function all()
    {
        return Cache::remember('showtimes_all', self::CACHE_TIME, function () {
            return Showtime::with(['Movie', 'Room'])->orderBy('StartTime', 'desc')->get();
        });
    }

    /**
     * ✅ Lấy dữ liệu phân trang (Chuyển câu query từ Controller xuống)
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
            
            $items      = $query->orderBy('StartTime', 'desc')
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

    /**
     * Tìm theo ID - ✅ ĐÃ CACHE
     */
    public function find($ShowtimeId)
    {
        return Cache::remember("showtime_detail_{$ShowtimeId}", self::CACHE_TIME, function () use ($ShowtimeId) {
            return Showtime::with(['Movie', 'Room'])->find($ShowtimeId);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $showtime = Showtime::create($data);
        $this->clearCache();
        return $showtime;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update(Showtime $showtime, array $data)
    {
        $showtime->update($data);
        $this->clearCache($showtime->ShowtimeId);
        return $showtime;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete(Showtime $showtime)
    {
        $id = $showtime->ShowtimeId;
        $result = $showtime->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('showtimes_all');
        
        // Xóa cache phân trang
        for ($page = 1; $page <= 10; $page++) {
            for ($limit = 10; $limit <= 100; $limit += 10) {
                Cache::forget("showtimes_paged_p{$page}_l{$limit}_" . md5('' . ''));
                Cache::forget("showtimes_paged_p{$page}_l{$limit}_" . md5('' . 'Scheduled'));
            }
        }

        if ($id) {
            Cache::forget("showtime_detail_{$id}");
        }
    }
}