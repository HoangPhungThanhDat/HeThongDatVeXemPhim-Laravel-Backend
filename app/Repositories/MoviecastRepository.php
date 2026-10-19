<?php

namespace App\Repositories;

use App\Models\Moviecast;
use Illuminate\Support\Facades\Cache;

class MoviecastRepository
{
    const CACHE_TIME = 3600; // Cache 1 tiếng (Diễn viên ít thay đổi)

    /**
     * Lấy tất cả - ✅ ĐÃ CACHE
     */
    public function all()
    {
        return Cache::remember('moviecasts_all', self::CACHE_TIME, function () {
            return Moviecast::with('movie')->orderBy('Name', 'asc')->get();
        });
    }

    /**
     * ✅ Lấy dữ liệu phân trang (Đã đưa câu query từ Controller xuống đây)
     */
    public function getPaged($page, $limit, $search = '', $role = '')
    {
        $cacheKey = "moviecasts_paged_p{$page}_l{$limit}_" . md5($search . $role);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $role) {
            $query = Moviecast::with('movie');

            if ($search) {
                $query->where('Name', 'like', "%{$search}%");
            }

            if ($role) {
                $query->where('Role', $role);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            
            $items      = $query->orderBy('CastId', 'desc')
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
    public function find($CastId)
    {
        return Cache::remember("moviecast_detail_{$CastId}", self::CACHE_TIME, function () use ($CastId) {
            return Moviecast::find($CastId);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $moviecast = Moviecast::create($data);
        $this->clearCache();
        return $moviecast;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update(Moviecast $moviecast, array $data)
    {
        $moviecast->update($data);
        $this->clearCache($moviecast->CastId);
        return $moviecast;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete(Moviecast $moviecast)
    {
        $id = $moviecast->CastId;
        $result = $moviecast->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('moviecasts_all');
        
        // Xóa cache phân trang (dọn dẹp các trang)
        for ($page = 1; $page <= 10; $page++) {
            for ($limit = 10; $limit <= 100; $limit += 10) {
                Cache::forget("moviecasts_paged_p{$page}_l{$limit}_" . md5('' . ''));
                Cache::forget("moviecasts_paged_p{$page}_l{$limit}_" . md5('' . 'Actor'));
                Cache::forget("moviecasts_paged_p{$page}_l{$limit}_" . md5('' . 'Director'));
            }
        }

        if ($id) {
            Cache::forget("moviecast_detail_{$id}");
        }
    }
}